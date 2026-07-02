<?php

namespace App\Livewire;

use App\Models\AiChatMessage;
use App\Services\OpenAiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatboxComponent extends Component
{
    public bool $isOpen = false;
    public string $newMessage = '';
    public array $chatHistory = [];
    public bool $isTyping = false;
    public string $sessionId = '';

    public function mount()
    {
        $this->sessionId = session('ai_chat_session_id', Str::uuid()->toString());
        session(['ai_chat_session_id' => $this->sessionId]);

        $this->loadHistory();
    }

    public function loadHistory()
    {
        $messages = AiChatMessage::where('user_id', Auth::id())
            ->where('session_id', $this->sessionId)
            ->orderBy('created_at')
            ->get(['role', 'message']);

        $this->chatHistory = $messages->map(function ($m) {
            return ['role' => $m->role, 'content' => $m->message];
        })->toArray();
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|max:2000',
        ]);

        $userMessage = trim($this->newMessage);
        $this->newMessage = '';

        // Save + show user message immediately
        $this->chatHistory[] = ['role' => 'user', 'content' => $userMessage];

        AiChatMessage::create([
            'user_id' => Auth::id(),
            'session_id' => $this->sessionId,
            'role' => 'user',
            'message' => $userMessage,
        ]);

        $this->isTyping = true;

        // Defer the actual API call to a follow-up request so "typing..." renders first
        $this->dispatch('message-sent');
    }

    #[On('trigger-ai-reply')]
    #[On('trigger-ai-reply')]
    public function getAiReply(OpenAiService $openAi)
    {
        $systemPrompt = 'You are a helpful assistant embedded inside a School Management System '
            . '(named school_db). Answer clearly and concisely. If asked about specific student, '
            . 'class, or institution data you do not have access to, tell the user to check the '
            . 'relevant module instead of guessing.';

        $apiMessages = collect($this->chatHistory)
            ->map(fn ($m) => ['role' => $m['role'], 'content' => $m['content']])
            ->toArray();

        $reply = $openAi->chat($apiMessages, $systemPrompt);

        $this->chatHistory[] = ['role' => 'assistant', 'content' => $reply];

        AiChatMessage::create([
            'user_id' => Auth::id(),
            'session_id' => $this->sessionId,
            'role' => 'assistant',
            'message' => $reply,
        ]);

        $this->isTyping = false;
    }

    public function clearChat()
    {
        AiChatMessage::where('user_id', Auth::id())
            ->where('session_id', $this->sessionId)
            ->delete();

        $this->chatHistory = [];
        $this->sessionId = \Illuminate\Support\Str::uuid()->toString();
        session(['ai_chat_session_id' => $this->sessionId]);
    }

    public function render()
    {
        return view('livewire.chatbox-component');
    }
}