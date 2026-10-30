<?php

declare(strict_types=1);

namespace FuteBus\AiAssistant\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around OpenAI Chat Completions API.
 * Uses Laravel's built-in HTTP client — no extra package needed.
 */
class LlmService
{
    private string $apiKey;

    private string $model;

    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.openai.api_key', '');
        $this->model = (string) config('services.openai.chat_model', 'gpt-4o-mini');
        $this->baseUrl = (string) config('services.openai.base_url', 'https://api.openai.com/v1');
    }

    /**
     * Send a chat-completion request and return the assistant text.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<int, array<string, mixed>>  $tools  OpenAI tool definitions
     * @return array{text: string|null, tool_calls: array<int, array<string, mixed>>|null}
     *
     * @throws \RuntimeException when the API key is not configured
     */
    public function chat(array $messages, array $tools = [], float $temperature = 0.3): array
    {
        if ($this->apiKey === '') {
            throw new \RuntimeException('OPENAI_API_KEY is not configured.');
        }

        $payload = [
            'model'       => $this->model,
            'messages'    => $messages,
            'temperature' => $temperature,
        ];

        if ($tools !== []) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->baseUrl($this->baseUrl)
                ->timeout(20)
                ->retry(2, 300)
                ->post('/chat/completions', $payload)
                ->throw();

            $choice = $response->json('choices.0');

            return [
                'text'       => $choice['message']['content'] ?? null,
                'tool_calls' => $choice['message']['tool_calls'] ?? null,
            ];
        } catch (RequestException $e) {
            Log::error('LlmService: OpenAI request failed', [
                'status' => $e->response?->status(),
                'body'   => $e->response?->body(),
            ]);
            throw $e;
        }
    }
}
