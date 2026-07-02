<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    protected ?string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        $this->model = config('services.groq.model', 'llama-3.3-70b-versatile');
    }

    /**
     * Send conversation history to Groq and get a reply.
     *
     * @param array $messages [['role' => 'user'|'assistant', 'content' => '...'], ...]
     * @param string|null $systemPrompt
     * @return string
     */
    public function chat(array $messages, ?string $systemPrompt = null): string
    {
        if (empty($this->apiKey)) {
            Log::error('Groq API key is missing. Check .env GROQ_API_KEY and run php artisan config:clear');
            return 'Groq API key সেট করা নেই। .env ফাইলে GROQ_API_KEY দিয়ে "php artisan config:clear" চালান।';
        }

        try {
            $chatMessages = [];

            if ($systemPrompt) {
                $chatMessages[] = ['role' => 'system', 'content' => $systemPrompt];
            }

            foreach ($messages as $m) {
                $chatMessages[] = ['role' => $m['role'], 'content' => $m['content']];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl, [
                'model' => $this->model,
                'messages' => $chatMessages,
                'max_tokens' => 1024,
            ]);

            if ($response->failed()) {
                Log::error('Groq API error', ['body' => $response->body()]);
                return 'দুঃখিত, এই মুহূর্তে উত্তর দিতে পারছি না। একটু পরে আবার চেষ্টা করুন।';
            }

            $data = $response->json();

            return $data['choices'][0]['message']['content'] ?? 'দুঃখিত, কোনো উত্তর পাওয়া যায়নি।';
        } catch (\Throwable $e) {
            Log::error('GroqService exception', ['message' => $e->getMessage()]);
            return 'সিস্টেমে একটি সমস্যা হয়েছে। অ্যাডমিনকে জানান।';
        }
    }
}