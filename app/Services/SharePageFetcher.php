<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Fetches share page HTML using HTTP, with an optional Playwright fallback.
 *
 * This class is responsible for:
 * - Fetching the share page with a standard HTTP client
 * - Detecting when the HTML is incomplete or blocked
 * - Falling back to Playwright for JavaScript-rendered pages
 */
final class SharePageFetcher
{
    /**
     * Fetch the share page HTML, using Playwright when necessary.
     */
    public function fetchSharePageHtml(string $shareUrl): string
    {
        // Request the share page with a browser-like profile.
        $httpResponse = Http::withHeaders([
            'User-Agent' => 'ChatProjects/1.0 (+https://chatgpt.com)',
            'Accept' => 'text/html,application/xhtml+xml',
        ])
            ->timeout(20)
            ->get($shareUrl);

        // Fail fast when the share URL is unreachable.
        if (!$httpResponse->successful()) {
            throw new RuntimeException("Failed to fetch share URL (HTTP {$httpResponse->status()}).");
        }

        $sharePageHtml = (string) $httpResponse->body();

        // Use Playwright if the response appears incomplete or blocked.
        if ($this->shouldUsePlaywright($sharePageHtml)) {
            $playwrightHtml = $this->fetchSharePageHtmlViaPlaywrightServer($shareUrl)
                ?? $this->fetchSharePageHtmlViaPlaywright($shareUrl);

            if ($playwrightHtml !== null) {
                return $playwrightHtml;
            }
        }

        return $sharePageHtml;
    }

    /**
     * Attempt to fetch the share page HTML via a Playwright HTTP service.
     */
    private function fetchSharePageHtmlViaPlaywrightServer(string $shareUrl): ?string
    {
        $playwrightEndpoint = $this->resolvePlaywrightEndpoint();

        // Skip the HTTP fallback when no endpoint is configured.
        if ($playwrightEndpoint === '') {
            return null;
        }

        try {
            $httpResponse = Http::timeout(60)->get($playwrightEndpoint, [
                'url' => $shareUrl,
            ]);
        } catch (Throwable) {
            // Network-level failures (DNS/connection refused) should not block local fallbacks.
            return null;
        }

        if (!$httpResponse->successful()) {
            return null;
        }

        $playwrightHtml = trim((string) $httpResponse->body());

        if ($playwrightHtml === '') {
            return null;
        }

        return $playwrightHtml;
    }

    /**
     * Resolve Playwright endpoint for the current runtime.
     */
    private function resolvePlaywrightEndpoint(): string
    {
        $playwrightEndpoint = trim((string) env('PLAYWRIGHT_SHARE_ENDPOINT', ''));

        if ($playwrightEndpoint === '') {
            return '';
        }

        // When running directly on Linux host, host.docker.internal often won't resolve.
        // Auto-rewrite to localhost so existing container-era env values keep working.
        if (
            PHP_OS_FAMILY === 'Linux'
            && !is_file('/.dockerenv')
            && str_contains($playwrightEndpoint, 'host.docker.internal')
        ) {
            return str_replace('host.docker.internal', '127.0.0.1', $playwrightEndpoint);
        }

        return $playwrightEndpoint;
    }

    /**
     * Determine whether Playwright should be used for this HTML.
     */
    private function shouldUsePlaywright(string $sharePageHtml): bool
    {
        // Cloudflare challenge pages should always trigger Playwright.
        if (str_contains($sharePageHtml, 'cf-mitigated') || str_contains($sharePageHtml, 'Just a moment')) {
            return true;
        }

        // Missing stream payloads usually indicate incomplete HTML.
        if (!str_contains($sharePageHtml, 'streamController.enqueue')) {
            return true;
        }

        // A single sanitized entry typically means only the prompt loaded.
        if (substr_count($sharePageHtml, '"sanitized"') < 2) {
            return true;
        }

        return false;
    }

    /**
     * Attempt to fetch the share page HTML using Playwright.
     */
    private function fetchSharePageHtmlViaPlaywright(string $shareUrl): ?string
    {
        $scriptPath = base_path('app/Debugging/playwright_fetch_share.mjs');

        // Skip Playwright when the helper script is unavailable.
        if (!is_file($scriptPath)) {
            return null;
        }

        $nodeBinary = env('PLAYWRIGHT_NODE_BIN', 'node');
        $command = escapeshellcmd($nodeBinary)
            . ' '
            . escapeshellarg($scriptPath)
            . ' '
            . escapeshellarg($shareUrl);

        $commandOutputLines = [];
        $exitCode = 0;

        // Capture stdout for HTML and ignore stderr to avoid noise.
        exec($command . ' 2>/dev/null', $commandOutputLines, $exitCode);

        if ($exitCode !== 0) {
            return null;
        }

        $playwrightHtml = trim(implode("\n", $commandOutputLines));

        if ($playwrightHtml === '') {
            return null;
        }

        return $playwrightHtml;
    }
}