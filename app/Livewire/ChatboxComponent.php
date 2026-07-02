<?php

namespace App\Livewire;

use App\Models\AiChatMessage;
use App\Services\RuleBasedChatService;
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
    public function getAiReply(RuleBasedChatService $bot)
    {
        $lastUserMessage = collect($this->chatHistory)
            ->where('role', 'user')
            ->last()['content'] ?? '';

        $reply = $bot->reply($lastUserMessage);

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