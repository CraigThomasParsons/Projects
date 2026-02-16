<?php

require 'vendor/autoload.php';

// Bootstrap the application so services are available.
$applicationInstance = require 'bootstrap/app.php';
$applicationInstance->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Resolve the services used in production.
$shareImporter = app(App\Services\ChatGptShareImporter::class);
$payloadInspector = app(App\Services\SharePayloadInspector::class);
$sharePageFetcher = app(App\Services\SharePageFetcher::class);

// Reflect into the importer so we can reuse its JSON candidate logic.
$importerReflection = new ReflectionClass($shareImporter);

$candidatesMethod = $importerReflection->getMethod('extractJsonPayloadCandidates');
$candidatesMethod->setAccessible(true);

// Fetch the share page HTML for inspection.
$sharePageHtml = $sharePageFetcher->fetchSharePageHtml('https://chatgpt.com/share/698a2387-16b8-8010-8a53-1c833c4403b0');

// Pull payload candidates using the same logic as the importer.
$jsonCandidates = $candidatesMethod->invoke($shareImporter, $sharePageHtml);
$streamCandidates = $payloadInspector->extractReactRouterStreamPayloads($sharePageHtml);

/**
 * Recursively search through a decoded JSON structure to find arrays that look like conversation payloads.
 * This is a heuristic approach that looks for keys like 'messages' or 'mapping' which are common in ChatGPT conversation exports.
 * 
 * @param array $data The decoded JSON data to search for conversation payloads
 * @param array $matches A reference to an array where found payload summaries will be stored
 * @param int $depth The current recursion depth to prevent infinite loops in deeply nested structures
 */
function findPayloads(array $data, array &$payloadMatches, int $depth = 0): void
{
    foreach ($data as $arrayKey => $arrayValue) {
        if (is_array($arrayValue) === false) {
            continue;
        }

        if (isset($arrayValue['messages']) && is_array($arrayValue['messages'])) {
            $payloadMatches[] = ['type' => 'messages', 'count' => count($arrayValue['messages'])];
        }

        if (isset($arrayValue['mapping']) && is_array($arrayValue['mapping'])) {
            $payloadMatches[] = ['type' => 'mapping', 'count' => count($arrayValue['mapping'])];
        }

        if ($depth < 6) {
            findPayloads($arrayValue, $payloadMatches, $depth + 1);
        }
    }
}

/**
 * Decode a candidate string and look for conversation payloads, printing a summary.
 * 
 * @param string $label A label to identify the candidate in output
 * @param string $candidate The raw candidate string to decode and inspect
 */
function summarizeCandidate(string $label, string $candidate): void
{
    $decoded = json_decode($candidate, true);
    if (is_array($decoded) === false) {
        echo $label . " decoded=false\n";
        return;
    }

    $payloadMatches = [];
    findPayloads($decoded, $payloadMatches);

    echo $label . ' decoded=true matches=' . count($payloadMatches) . "\n";
    foreach (array_slice($payloadMatches, 0, 5) as $payloadMatch) {
        echo '  - ' . $payloadMatch['type'] . ' count=' . $payloadMatch['count'] . "\n";
    }
}

$candidateIndex = 0;
foreach ($jsonCandidates as $candidate) {
    summarizeCandidate('json[' . $candidateIndex . ']', $candidate);
    $candidateIndex++;
}

$candidateIndex = 0;
foreach ($streamCandidates as $candidate) {
    summarizeCandidate('stream[' . $candidateIndex . ']', $candidate);
    $candidateIndex++;
}

// Provide a quick preview of extracted messages.
$streamMessages = $payloadInspector->extractStreamMessages($streamCandidates);
echo 'stream_messages=' . count($streamMessages) . "\n";
foreach (array_slice($streamMessages, 0, 5) as $streamMessage) {
    echo strtoupper($streamMessage['author']) . ': ' . substr($streamMessage['text'], 0, 160) . "\n";
}