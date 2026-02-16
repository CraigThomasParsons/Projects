<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Conversation;
use App\Services\SharePageFetcher;
use App\Services\SharePayloadInspector;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Imports a shared ChatGPT conversation into local storage.
 *
 * This service is responsible for:
 * - Fetching the public share URL HTML
 * - Extracting the embedded conversation payload
 * - Normalizing messages into readable markdown
 * - Persisting the markdown and metadata to storage
 */
final class ChatGptShareImporter
{
    /**
     * Import a shared conversation and persist markdown.
     *
     * @throws RuntimeException
     */
    public function import(Conversation $conversation, string $shareUrl): void
    {
        // Fetch the share page so we can parse the embedded payload.
        $sharePageHtml = app(SharePageFetcher::class)->fetchSharePageHtml($shareUrl);

        // Locate the structured conversation payload in the page.
        $conversationPayload = $this->extractConversationPayload($sharePageHtml);

        // Guard against missing payloads to avoid saving empty conversations.
        if ($conversationPayload === null) {
            throw new RuntimeException('Unable to locate conversation payload.');
        }

        // Extract title and messages into a normalized markdown representation.
        $conversationTitle = $this->extractConversationTitle($conversationPayload);
        $normalizedMessages = $this->extractNormalizedMessages($conversationPayload);

        // Fall back to stream payload parsing when the JSON payload lacks messages.
        if (empty($normalizedMessages)) {
            $payloadInspector = app(SharePayloadInspector::class);
            $streamPayloads = $payloadInspector->extractReactRouterStreamPayloads($sharePageHtml);
            $normalizedMessages = $payloadInspector->extractStreamMessages($streamPayloads);
        }
        $markdownDocument = $this->buildMarkdownDocument($conversationTitle, $normalizedMessages, $shareUrl);

        // Persist the metadata and raw content for future display.
        $conversation->title = $conversationTitle ?: $conversation->title;
        $conversation->raw_content = $markdownDocument;
        $conversation->save();

        // Write the markdown file so it can be referenced outside the database.
        $markdownFilename = $this->buildMarkdownFilename($conversation, $conversationTitle);

        try {
            // Ensure the target directory exists before writing.
            Storage::disk('local')->makeDirectory('conversations');
            Storage::disk('local')->put($markdownFilename, $markdownDocument);
        } catch (Throwable $exception) {
            // Log the failure but keep the database content intact.
            report($exception);
        }
    }

    /**
     * Extract the conversation payload from the share page HTML.
     */
    private function extractConversationPayload(string $sharePageHtml): ?array
    {
        // Attempt structured JSON entry points first.
        $jsonPayloadCandidates = $this->extractJsonPayloadCandidates($sharePageHtml);

        // Include React Router stream payloads from newer share pages.
        $payloadInspector = app(SharePayloadInspector::class);
        $reactRouterPayloads = $payloadInspector->extractReactRouterStreamPayloads($sharePageHtml);
        foreach ($reactRouterPayloads as $reactRouterPayload) {
            $jsonPayloadCandidates[] = $reactRouterPayload;
        }

        foreach ($jsonPayloadCandidates as $jsonPayloadCandidate) {
            // Decode the candidate so we can locate nested conversation data.
            $decodedStructure = json_decode($jsonPayloadCandidate, true);

            // Skip invalid JSON blocks early.
            if (!is_array($decodedStructure)) {
                continue;
            }

            $conversationPayload = $this->findConversationPayload($decodedStructure);

            if ($conversationPayload !== null) {
                return $conversationPayload;
            }

            // Some stream payloads are not valid JSON, so parse them explicitly.
            $streamPayload = $this->extractStreamConversationPayload($jsonPayloadCandidate);
            if ($streamPayload !== null) {
                return $streamPayload;
            }
        }

        // Fall back to searching for embedded payload keys in the raw HTML.
        $conversationPayload = $this->extractPayloadByKey($sharePageHtml, 'sharedConversation')
            ?? $this->extractPayloadByKey($sharePageHtml, 'conversation');

        if ($conversationPayload !== null) {
            return $conversationPayload;
        }

        return null;
    }

    /**
     * Attempt to build a conversation payload from React Router stream data.
     */
    private function extractStreamConversationPayload(string $decodedStream): ?array
    {
        // Ignore streams that do not contain the mapping payload.
        if (strpos($decodedStream, '"mapping"') === false) {
            return null;
        }

        $mapping = $this->extractStreamObject($decodedStream, 'mapping');
        if ($mapping === null) {
            // Bail out if the mapping object cannot be reconstructed.
            return null;
        }

        $title = $this->extractStreamStringValue($decodedStream, 'title');
        $currentNode = $this->extractStreamStringValue($decodedStream, 'current_node')
            ?? $this->extractStreamStringValue($decodedStream, 'currentNode');

        return array_filter([
            'title' => $title,
            'current_node' => $currentNode,
            'mapping' => $mapping,
        ], static fn ($value) => $value !== null);
    }

    /**
     * Extract an object literal that follows a stream key like "mapping".
     */
    private function extractStreamObject(string $decodedStream, string $key): ?array
    {
        $needle = '"' . $key . '"';
        $needlePosition = strpos($decodedStream, $needle);
        if ($needlePosition === false) {
            // Stop if the stream does not include the requested key.
            return null;
        }

        $objectStartPosition = strpos($decodedStream, '{', $needlePosition);
        if ($objectStartPosition === false) {
            // Guard against malformed streams with missing objects.
            return null;
        }

        $jsonPayload = $this->extractJsonObject($decodedStream, $objectStartPosition);
        if ($jsonPayload === null) {
            // Avoid decoding partial JSON fragments.
            return null;
        }

        $decodedPayload = json_decode($jsonPayload, true);
        if (!is_array($decodedPayload)) {
            // Ensure the payload is valid JSON before returning it.
            return null;
        }

        return $decodedPayload;
    }

    /**
     * Extract a string value that follows a stream key like "title".
     */
    private function extractStreamStringValue(string $decodedStream, string $key): ?string
    {
        $needle = '"' . $key . '"';
        $needlePosition = strpos($decodedStream, $needle);
        if ($needlePosition === false) {
            // Skip missing keys so we do not misread unrelated values.
            return null;
        }

        $valueStart = strpos($decodedStream, '"', $needlePosition + strlen($needle));
        if ($valueStart === false) {
            // Guard against streams that omit the expected quoted value.
            return null;
        }

        $cursor = $valueStart + 1;
        $buffer = '';
        $isEscaped = false;
        $length = strlen($decodedStream);

        for (; $cursor < $length; $cursor++) {
            $character = $decodedStream[$cursor];

            if ($isEscaped) {
                // Preserve escape sequences inside the stream value.
                $buffer .= $character;
                $isEscaped = false;
                continue;
            }

            if ($character === '\\') {
                // Track escapes to avoid terminating inside a value.
                $isEscaped = true;
                continue;
            }

            if ($character === '"') {
                // End of the quoted value.
                break;
            }

            $buffer .= $character;
        }

        // Return null for empty values so callers can fall back safely.
        return $buffer !== '' ? $buffer : null;
    }

    /**
     * Collect JSON payload candidates from common Next.js entry points.
     */
    private function extractJsonPayloadCandidates(string $sharePageHtml): array
    {
        $jsonPayloadCandidates = [];

        // Grab the __NEXT_DATA__ script tag if it exists.
        $nextDataPayload = $this->extractNextDataJson($sharePageHtml);
        if ($nextDataPayload !== null) {
            $jsonPayloadCandidates[] = $nextDataPayload;
        }

        // Some pages attach the data payload to window.__NEXT_DATA__.
        $windowDataPayload = $this->extractWindowDataJson($sharePageHtml);
        if ($windowDataPayload !== null) {
            $jsonPayloadCandidates[] = $windowDataPayload;
        }

        return $jsonPayloadCandidates;
    }

    /**
     * Extract the __NEXT_DATA__ JSON block from the HTML.
     */
    private function extractNextDataJson(string $sharePageHtml): ?string
    {
        if (preg_match('/<script[^>]+id="__NEXT_DATA__"[^>]*>(.*?)<\/script>/s', $sharePageHtml, $matches)) {
            return html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
        }

        return null;
    }

    /**
     * Extract the window.__NEXT_DATA__ JSON block from the HTML.
     */
    private function extractWindowDataJson(string $sharePageHtml): ?string
    {
        if (preg_match('/window\.__NEXT_DATA__\s*=\s*({.*?})\s*;?/s', $sharePageHtml, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Attempt to extract a payload object by a specific JSON key.
     */
    private function extractPayloadByKey(string $sharePageHtml, string $payloadKey): ?array
    {
        $searchNeedle = '"' . $payloadKey . '"';
        $needlePosition = strpos($sharePageHtml, $searchNeedle);

        // Guard against missing keys so we do not parse unrelated content.
        if ($needlePosition === false) {
            return null;
        }

        $colonPosition = strpos($sharePageHtml, ':', $needlePosition + strlen($searchNeedle));
        if ($colonPosition === false) {
            return null;
        }

        $objectStartPosition = strpos($sharePageHtml, '{', $colonPosition);
        if ($objectStartPosition === false) {
            return null;
        }

        $jsonPayload = $this->extractJsonObject($sharePageHtml, $objectStartPosition);
        if ($jsonPayload === null) {
            return null;
        }

        $decodedPayload = json_decode($jsonPayload, true);
        if (!is_array($decodedPayload)) {
            return null;
        }

        if ($this->looksLikeConversationPayload($decodedPayload)) {
            return $decodedPayload;
        }

        return $this->findConversationPayload($decodedPayload);
    }

    /**
     * Extract a JSON object from a string by balancing braces.
     */
    private function extractJsonObject(string $sourceText, int $startPosition): ?string
    {
        $braceDepth = 0;
        $isInsideString = false;
        $isEscaped = false;
        $sourceLength = strlen($sourceText);

        for ($characterIndex = $startPosition; $characterIndex < $sourceLength; $characterIndex++) {
            $currentCharacter = $sourceText[$characterIndex];

            if ($isEscaped) {
                $isEscaped = false;
                continue;
            }

            if ($currentCharacter === '\\') {
                $isEscaped = true;
                continue;
            }

            if ($currentCharacter === '"') {
                $isInsideString = !$isInsideString;
                continue;
            }

            if ($isInsideString) {
                continue;
            }

            if ($currentCharacter === '{') {
                $braceDepth++;
                continue;
            }

            if ($currentCharacter === '}') {
                $braceDepth--;

                if ($braceDepth === 0) {
                    return substr($sourceText, $startPosition, $characterIndex - $startPosition + 1);
                }
            }
        }

        return null;
    }

    /**
     * Recursively scan a decoded structure for a conversation payload.
     */
    private function findConversationPayload(array $decodedStructure): ?array
    {
        if ($this->looksLikeConversationPayload($decodedStructure)) {
            return $decodedStructure;
        }

        foreach ($decodedStructure as $decodedValue) {
            if (!is_array($decodedValue)) {
                continue;
            }

            if (isset($decodedValue['conversation']) && is_array($decodedValue['conversation'])) {
                if ($this->looksLikeConversationPayload($decodedValue['conversation'])) {
                    return $decodedValue['conversation'];
                }
            }

            if (isset($decodedValue['sharedConversation']) && is_array($decodedValue['sharedConversation'])) {
                if ($this->looksLikeConversationPayload($decodedValue['sharedConversation'])) {
                    return $decodedValue['sharedConversation'];
                }
            }

            $nestedPayload = $this->findConversationPayload($decodedValue);

            if ($nestedPayload !== null) {
                return $nestedPayload;
            }
        }

        return null;
    }

    /**
     * Determine if an array resembles the conversation payload structure.
     */
    private function looksLikeConversationPayload(array $decodedPayload): bool
    {
        if (isset($decodedPayload['messages']) && is_array($decodedPayload['messages'])) {
            return true;
        }

        if (!isset($decodedPayload['mapping']) || !is_array($decodedPayload['mapping'])) {
            return false;
        }

        return $this->mappingContainsMessageNodes($decodedPayload['mapping']);
    }

    /**
     * Confirm that a mapping payload contains message nodes, not just IDs.
     */
    private function mappingContainsMessageNodes(array $mapping): bool
    {
        foreach ($mapping as $mappingNode) {
            if (!is_array($mappingNode)) {
                continue;
            }

            if (isset($mappingNode['message']) && is_array($mappingNode['message'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Pull the human-readable title from the payload.
     */
    private function extractConversationTitle(array $conversationPayload): ?string
    {
        $titleValue = Arr::get($conversationPayload, 'title');

        if (is_string($titleValue) && trim($titleValue) !== '') {
            return trim($titleValue);
        }

        return null;
    }

    /**
     * Extract and normalize all messages from the payload.
     */
    private function extractNormalizedMessages(array $conversationPayload): array
    {
        if (isset($conversationPayload['messages']) && is_array($conversationPayload['messages'])) {
            return $this->normalizeMessages($conversationPayload['messages']);
        }

        if (isset($conversationPayload['mapping']) && is_array($conversationPayload['mapping'])) {
            $currentNodeIdentifier = $conversationPayload['current_node'] ?? $conversationPayload['currentNode'] ?? null;
            $normalizedMessages = $this->extractMessagesFromMapping($conversationPayload['mapping'], $currentNodeIdentifier);

            if (!empty($normalizedMessages)) {
                return $normalizedMessages;
            }
        }

        return [];
    }

    /**
     * Normalize an array of message objects.
     */
    private function normalizeMessages(array $messages): array
    {
        $normalizedMessages = [];

        foreach ($messages as $message) {
            if (!is_array($message)) {
                continue;
            }

            $normalizedMessages[] = $this->normalizeMessage($message);
        }

        return array_values(array_filter($normalizedMessages));
    }

    /**
     * Extract messages from the mapping-based conversation payload.
     */
    private function extractMessagesFromMapping(array $mapping, ?string $currentNodeIdentifier): array
    {
        if ($currentNodeIdentifier && isset($mapping[$currentNodeIdentifier])) {
            $pathNodeIdentifiers = [];
            $nodeIdentifier = $currentNodeIdentifier;

            while ($nodeIdentifier) {
                $pathNodeIdentifiers[] = $nodeIdentifier;
                $nodeIdentifier = $mapping[$nodeIdentifier]['parent'] ?? null;
            }

            $pathNodeIdentifiers = array_reverse($pathNodeIdentifiers);

            $normalizedMessages = [];
            foreach ($pathNodeIdentifiers as $pathNodeIdentifier) {
                $mappingNode = $mapping[$pathNodeIdentifier] ?? null;
                if (!is_array($mappingNode) || empty($mappingNode['message'])) {
                    continue;
                }
                $normalizedMessage = $this->normalizeMessage($mappingNode['message']);
                if ($normalizedMessage) {
                    $normalizedMessages[] = $normalizedMessage;
                }
            }

            return $normalizedMessages;
        }

        $rootNodeIdentifier = $this->findRootNodeIdentifier($mapping);

        if ($rootNodeIdentifier === null) {
            return [];
        }

        $normalizedMessages = [];
        $this->traverseMapping($mapping, $rootNodeIdentifier, $normalizedMessages);

        return $normalizedMessages;
    }

    /**
     * Find the root node identifier from the mapping structure.
     */
    private function findRootNodeIdentifier(array $mapping): ?string
    {
        foreach ($mapping as $nodeIdentifier => $mappingNode) {
            if (!is_array($mappingNode)) {
                continue;
            }

            if (!array_key_exists('parent', $mappingNode) || $mappingNode['parent'] === null) {
                return (string) $nodeIdentifier;
            }
        }

        return null;
    }

    /**
     * Traverse the mapping tree and collect messages in order.
     */
    private function traverseMapping(array $mapping, string $nodeIdentifier, array &$normalizedMessages): void
    {
        $mappingNode = $mapping[$nodeIdentifier] ?? null;

        if (!is_array($mappingNode)) {
            return;
        }

        if (!empty($mappingNode['message'])) {
            $normalizedMessage = $this->normalizeMessage($mappingNode['message']);
            if ($normalizedMessage) {
                $normalizedMessages[] = $normalizedMessage;
            }
        }

        $childNodeIdentifiers = $mappingNode['children'] ?? [];

        if (!is_array($childNodeIdentifiers)) {
            return;
        }

        foreach ($childNodeIdentifiers as $childNodeIdentifier) {
            if (isset($mapping[$childNodeIdentifier])) {
                $this->traverseMapping($mapping, (string) $childNodeIdentifier, $normalizedMessages);
            }
        }
    }

    /**
     * Normalize a single message into a simple author/text structure.
     */
    private function normalizeMessage(array $message): ?array
    {
        $authorRole = $message['author']['role'] ?? $message['author']['name'] ?? null;
        $contentPayload = $message['content'] ?? null;

        $contentText = $this->extractContentText($contentPayload);

        // Guard against empty messages so markdown stays readable.
        if ($contentText === null || trim($contentText) === '') {
            return null;
        }

        return [
            'author' => $authorRole ? (string) $authorRole : 'unknown',
            'text' => trim($contentText),
        ];
    }

    /**
     * Extract text content from a message content payload.
     */
    private function extractContentText(mixed $contentPayload): ?string
    {
        if (is_string($contentPayload)) {
            return $contentPayload;
        }

        if (!is_array($contentPayload)) {
            return null;
        }

        if (isset($contentPayload['parts']) && is_array($contentPayload['parts'])) {
            $contentParts = [];
            foreach ($contentPayload['parts'] as $contentPart) {
                if (is_string($contentPart)) {
                    $contentParts[] = $contentPart;
                    continue;
                }
                if (is_array($contentPart) && isset($contentPart['text']) && is_string($contentPart['text'])) {
                    $contentParts[] = $contentPart['text'];
                }
            }
            return implode("\n\n", $contentParts);
        }

        if (isset($contentPayload['text']) && is_string($contentPayload['text'])) {
            return $contentPayload['text'];
        }

        if (isset($contentPayload['content']) && is_string($contentPayload['content'])) {
            return $contentPayload['content'];
        }

        return null;
    }


    /**
     * Build markdown content from normalized messages.
     */
    private function buildMarkdownDocument(?string $conversationTitle, array $normalizedMessages, string $shareUrl): string
    {
        $markdownLines = [];

        if ($conversationTitle) {
            $markdownLines[] = '# ' . $conversationTitle;
            $markdownLines[] = '';
        }

        $markdownLines[] = "Share URL: {$shareUrl}";
        $markdownLines[] = '';

        foreach ($normalizedMessages as $normalizedMessage) {
            $authorHeading = ucfirst($normalizedMessage['author']);
            $markdownLines[] = "## {$authorHeading}";
            $markdownLines[] = '';
            $markdownLines[] = $normalizedMessage['text'];
            $markdownLines[] = '';
        }

        return rtrim(implode("\n", $markdownLines)) . "\n";
    }

    /**
     * Build the markdown filename used in storage.
     */
    private function buildMarkdownFilename(Conversation $conversation, ?string $conversationTitle): string
    {
        $titleSlug = $conversationTitle ? Str::slug($conversationTitle) : 'conversation';

        return "conversations/{$conversation->id}-{$titleSlug}.md";
    }
}
