<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ConversationImportController extends Controller
{
    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeRequest($request);

        $validated = $request->validate([
            'markdown' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'share_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $conversation->raw_content = $validated['markdown'];

        if (!empty($validated['title'])) {
            $conversation->title = $validated['title'];
        }

        if (!empty($validated['share_url'])) {
            $conversation->share_url = $validated['share_url'];
        }

        $conversation->save();

        $filename = $this->buildMarkdownFilename($conversation, $validated['title'] ?? null);
        Storage::disk('local')->put($filename, $validated['markdown']);

        return response()->json([
            'status' => 'ok',
            'conversation_id' => $conversation->id,
            'markdown_path' => $filename,
        ]);
    }

    private function authorizeRequest(Request $request): void
    {
        $token = config('services.piper.token');

        if (empty($token)) {
            abort(500, 'Piper token not configured.');
        }

        $provided = $request->bearerToken()
            ?? $request->header('X-Piper-Token');

        if ($provided !== $token) {
            abort(403, 'Invalid token.');
        }
    }

    private function buildMarkdownFilename(Conversation $conversation, ?string $title): string
    {
        $slug = $title ? Str::slug($title) : 'conversation';

        return "conversations/{$conversation->id}-{$slug}.md";
    }
}
