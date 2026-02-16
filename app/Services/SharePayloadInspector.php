<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Provides helper utilities for inspecting share page payloads.
 *
 * This class is responsible for:
 * - Extracting message text from React Router stream payloads
 * - Applying best-effort role labels to those messages
 * - Deduplicating noisy or repeated payload fragments
 */
final class SharePayloadInspector
{
    /**
     * Extract React Router stream payloads embedded in share pages.
     */
    public function extractReactRouterStreamPayloads(string $sharePageHtml): array
    {
        $payloads = [];

        $needle = 'streamController.enqueue("';
        $offset = 0;
        $htmlLength = strlen($sharePageHtml);

        while (($start = strpos($sharePageHtml, $needle, $offset)) !== false) {
            // Move the cursor to the start of the streamed string content.
            $cursor = $start + strlen($needle);
            $isEscaped = false;
            $buffer = '';

            for (; $cursor < $htmlLength; $cursor++) {
                $character = $sharePageHtml[$cursor];

                if ($isEscaped) {
                    // Preserve escaped characters so the payload remains intact.
                    $buffer .= $character;
                    $isEscaped = false;
                    continue;
                }

                if ($character === '\\') {
                    // Track escape sequences to avoid terminating early.
                    $buffer .= $character;
                    $isEscaped = true;
                    continue;
                }

                if ($character === '"') {
                    // The next unescaped quote ends the streamed payload.
                    break;
                }

                $buffer .= $character;
            }

            if ($buffer !== '') {
                // Unescape the payload so JSON parsing sees the real content.
                $payloads[] = stripcslashes($buffer);
            }

            // Continue searching after the closing quote we just processed.
            $offset = $cursor + 1;
        }

        return $payloads;
    }

    /**
     * Extract normalized messages from React Router stream payloads.
     */
    public function extractStreamMessages(array $streamPayloads): array
    {
        $normalizedMessages = [];
        $messageHashes = [];

        foreach ($streamPayloads as $streamPayload) {
            // Skip empty payloads so we do not process noise.
            if (!is_string($streamPayload) || trim($streamPayload) === '') {
                continue;
            }

            $segmentMessages = $this->extractMessagesFromPayload($streamPayload);
            foreach ($segmentMessages as $segmentMessage) {
                $this->appendStreamMessage($normalizedMessages, $messageHashes, $segmentMessage);
            }
        }

        return $normalizedMessages;
    }

    /**
     * Extract messages from a single stream payload.
     */
    private function extractMessagesFromPayload(string $streamPayload): array
    {
        $normalizedMessages = [];

        $messageSegments = $this->extractMessageSegments($streamPayload);
        foreach ($messageSegments as $messageSegment) {
            $authorRole = $this->extractRoleFromSegment($messageSegment) ?? 'message';
            $sanitizedText = $this->extractSanitizedTextFromSegment($messageSegment);
            $textValue = $sanitizedText ?? $this->extractTextValueFromSegment($messageSegment);

            if ($textValue === null) {
                continue;
            }

            $normalizedMessages[] = [
                'author' => $authorRole,
                'text' => $textValue,
            ];
        }

        // Merge fallback text blocks so missing roles still appear.
        $fallbackTexts = array_merge(
            $this->extractAllSanitizedTexts($streamPayload),
            $this->extractAllTextValues($streamPayload)
        );

        foreach ($fallbackTexts as $fallbackText) {
            $normalizedMessages[] = [
                'author' => 'message',
                'text' => $fallbackText,
            ];
        }

        return $normalizedMessages;
    }

    /**
     * Split the stream payload into message-sized segments.
     */
    private function extractMessageSegments(string $streamPayload): array
    {
        $messageSegments = [];
        $segmentMatches = [];

        preg_match_all('/"message"/', $streamPayload, $segmentMatches, PREG_OFFSET_CAPTURE);

        foreach ($segmentMatches[0] ?? [] as $segmentMatch) {
            $segmentOffset = $segmentMatch[1] ?? null;
            if (!is_int($segmentOffset)) {
                continue;
            }

            // Slice a window after the message marker to capture role + content.
            $messageSegments[] = substr($streamPayload, $segmentOffset, 3500);
        }

        return $messageSegments;
    }

    /**
     * Extract the author role from a segment if present.
     */
    private function extractRoleFromSegment(string $messageSegment): ?string
    {
        $roleMatches = [];

        if (preg_match('/"role","(user|assistant|system)"/', $messageSegment, $roleMatches) !== 1) {
            return null;
        }

        return $roleMatches[1] ?? null;
    }

    /**
     * Extract the sanitized message text from a segment.
     */
    private function extractSanitizedTextFromSegment(string $messageSegment): ?string
    {
        $sanitizedMatches = [];

        if (preg_match('/"sanitized","([^"]*)"/', $messageSegment, $sanitizedMatches) !== 1) {
            return null;
        }

        $sanitizedText = stripcslashes($sanitizedMatches[1] ?? '');

        if (trim($sanitizedText) === '') {
            return null;
        }

        return $sanitizedText;
    }

    /**
     * Extract a text value from a segment when sanitized text is missing.
     */
    private function extractTextValueFromSegment(string $messageSegment): ?string
    {
        $textMatches = [];

        if (preg_match('/"text","([^"]*)"/', $messageSegment, $textMatches) !== 1) {
            return null;
        }

        $textValue = stripcslashes($textMatches[1] ?? '');

        if (trim($textValue) === '') {
            return null;
        }

        return $textValue;
    }

    /**
     * Extract all sanitized text blocks from the payload as a fallback.
     */
    private function extractAllSanitizedTexts(string $streamPayload): array
    {
        $sanitizedTexts = [];
        $sanitizedMatches = [];

        preg_match_all('/"sanitized","([^"]*)"/', $streamPayload, $sanitizedMatches);

        foreach ($sanitizedMatches[1] ?? [] as $sanitizedMatch) {
            $sanitizedText = stripcslashes($sanitizedMatch);

            if (trim($sanitizedText) === '') {
                continue;
            }

            $sanitizedTexts[] = $sanitizedText;
        }

        return $sanitizedTexts;
    }

    /**
     * Extract all text values from the payload as an additional fallback.
     */
    private function extractAllTextValues(string $streamPayload): array
    {
        $textValues = [];
        $textMatches = [];

        preg_match_all('/"text","([^"]*)"/', $streamPayload, $textMatches);

        foreach ($textMatches[1] ?? [] as $textMatch) {
            $textValue = stripcslashes($textMatch);

            if (trim($textValue) === '') {
                continue;
            }

            $textValues[] = $textValue;
        }

        return $textValues;
    }

    /**
     * Add a stream message to the normalized list with deduplication.
     */
    private function appendStreamMessage(array &$normalizedMessages, array &$messageHashes, array $segmentMessage): void
    {
        $authorRole = $segmentMessage['author'] ?? 'message';
        $rawText = $segmentMessage['text'] ?? '';

        // Ignore empty message bodies so the transcript stays readable.
        if (trim($rawText) === '') {
            return;
        }

        // Normalize spacing while preserving line breaks.
        $normalizedText = preg_replace('/[ \t]+/', ' ', $rawText) ?? $rawText;
        $normalizedText = trim($normalizedText);

        $messageHash = md5($authorRole . '|' . $normalizedText);

        // Skip duplicates that appear in multiple payload segments.
        if (isset($messageHashes[$messageHash])) {
            return;
        }

        $messageHashes[$messageHash] = true;

        $normalizedMessages[] = [
            'author' => $authorRole,
            'text' => $normalizedText,
        ];
    }
}
