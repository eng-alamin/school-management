<?php

namespace App\Livewire\Teacher\Mailbox;

use App\Models\Message;
use Livewire\Component;
use Livewire\WithPagination;

class ImportantComponent extends Component
{
    use WithPagination;

    public ?int     $viewingId = null;
    public ?Message $viewing   = null;
    public string   $search    = '';

    protected $queryString = ['search'];

    /* ------------------------------------------------------------------ */

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /* ------------------------------------------------------------------ */

    public function viewMessage(int $id): void
    {
        $userId  = auth()->id();
        $message = Message::important($userId)->with('sender', 'receiver')->findOrFail($id);

        // Mark as read if current user is receiver
        if ($message->receiver_id === $userId) {
            $message->markAsRead();
        }

        $this->viewing   = $message;
        $this->viewingId = $id;
    }

    public function backToList(): void
    {
        $this->viewing   = null;
        $this->viewingId = null;
    }

    /* ------------------------------------------------------------------ */

    public function unmarkImportant(int $id): void
    {
        Message::important(auth()->id())->findOrFail($id)
               ->update(['is_important' => false]);

        if ($this->viewingId === $id) {
            $this->backToList();
        }

        $this->dispatch('notify', type: 'info', msg: 'Removed from important.');
    }

    /* ------------------------------------------------------------------ */

    public function render()
    {
        $userId = auth()->id();

        $messages = Message::important($userId)
            ->with('sender', 'receiver')
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('subject', 'like', "%{$this->search}%")
                       ->orWhere('body', 'like', "%{$this->search}%")
                )
            )
            ->latest()
            ->paginate(15);

        return view('livewire.teacher.mailbox.important-component', compact('messages'))
            ->layout('layouts.teacher.app', [
                'title' => 'MailBox | ' . institution()->name,
            ]);
    }
}
