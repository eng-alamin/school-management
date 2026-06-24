<?php

namespace App\Livewire\Admin\Mailbox;

use App\Models\Message;
use App\Models\User;
use Livewire\Component;

class ComposeComponent extends Component
{
    public string $to         = '';
    public string $subject    = '';
    public string $body       = '';
    public string $searchUser = '';

    public ?int $receiver_id  = null;
    public string $receiverName = '';

    public array $userSuggestions = [];

    /* ------------------------------------------------------------------ */

    protected array $rules = [
        'receiver_id' => 'required|exists:users,id',
        'subject'     => 'required|min:2|max:255',
        'body'        => 'required|min:5',
    ];

    protected array $messages = [
        'receiver_id.required' => 'Please select a recipient.',
        'subject.required'     => 'Subject is required.',
        'body.required'        => 'Message body is required.',
    ];

    /* ------------------------------------------------------------------ */

    public function updatedSearchUser(string $value): void
    {
        if (strlen($value) < 2) {
            $this->userSuggestions = [];
            return;
        }

        $this->userSuggestions = User::where('id', '!=', auth()->id())
            ->where(function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%")
                  ->orWhere('email', 'like', "%{$value}%");
            })
            ->limit(8)
            ->get(['id', 'name', 'email'])
            ->toArray();
    }

    public function selectUser(int $id, string $name): void
    {
        $this->receiver_id      = $id;
        $this->receiverName     = $name;
        $this->searchUser       = $name;
        $this->userSuggestions  = [];
    }

    public function clearReceiver(): void
    {
        $this->receiver_id      = null;
        $this->receiverName     = '';
        $this->searchUser       = '';
        $this->userSuggestions  = [];
    }

    /* ------------------------------------------------------------------ */

    public function send(): void
    {
        $this->validate();

        Message::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $this->receiver_id,
            'subject'     => $this->subject,
            'body'        => $this->body,
        ]);

        session()->flash('success', 'Message sent successfully!');
        $this->reset(['receiver_id', 'receiverName', 'searchUser', 'subject', 'body']);
    }

    public function saveDraft(): void
    {
        // Extend later: add `is_draft` column to messages table
        session()->flash('info', 'Draft feature coming soon.');
    }

    /* ------------------------------------------------------------------ */

    public function render()
    {
        return view('livewire.admin.mailbox.compose-component')
            ->layout('layouts.admin.app', [
                'title' => 'MailBox | ' . institution()->name,
            ]);
    }
}
