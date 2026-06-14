<?php

namespace App\Livewire\Accountant\Mailbox;

use App\Models\Message;
use Livewire\Component;
use Livewire\WithPagination;

class TrashComponent extends Component
{
    use WithPagination;

    public ?int     $viewingId    = null;
    public ?Message $viewing      = null;
    public string   $search       = '';
    public bool     $confirmEmpty = false;

    protected $queryString = ['search'];

    /* ------------------------------------------------------------------ */

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /* ------------------------------------------------------------------ */

    public function viewMessage(int $id): void
    {
        $this->viewing   = Message::trash(auth()->id())->with('sender', 'receiver')->findOrFail($id);
        $this->viewingId = $id;
    }

    public function backToList(): void
    {
        $this->viewing   = null;
        $this->viewingId = null;
    }

    /* ------------------------------------------------------------------ */

    public function restoreMessage(int $id): void
    {
        $userId  = auth()->id();
        $message = Message::trash($userId)->findOrFail($id);

        if ($message->receiver_id === $userId) {
            $message->update(['is_trashed_by_receiver' => false]);
        } else {
            $message->update(['is_trashed_by_sender' => false]);
        }

        if ($this->viewingId === $id) {
            $this->backToList();
        }

        $this->dispatch('notify', type: 'success', msg: 'Message restored.');
    }

    public function permanentDelete(int $id): void
    {
        $userId  = auth()->id();
        $message = Message::trash($userId)->findOrFail($id);

        if ($message->receiver_id === $userId) {
            $message->update(['is_deleted_by_receiver' => true]);
        } else {
            $message->update(['is_deleted_by_sender' => true]);
        }

        if ($this->viewingId === $id) {
            $this->backToList();
        }

        $this->dispatch('notify', type: 'error', msg: 'Message permanently deleted.');
    }

    public function emptyTrash(): void
    {
        $userId   = auth()->id();
        $messages = Message::trash($userId)->get();

        foreach ($messages as $message) {
            if ($message->receiver_id === $userId) {
                $message->update(['is_deleted_by_receiver' => true]);
            } else {
                $message->update(['is_deleted_by_sender' => true]);
            }
        }

        $this->confirmEmpty = false;
        $this->dispatch('notify', type: 'error', msg: 'Trash emptied.');
    }

    /* ------------------------------------------------------------------ */

    public function render()
    {
        $messages = Message::trash(auth()->id())
            ->with('sender', 'receiver')
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('subject', 'like', "%{$this->search}%")
                       ->orWhere('body', 'like', "%{$this->search}%")
                )
            )
            ->latest()
            ->paginate(15);

        return view('livewire.accountant.mailbox.trash-component', compact('messages'))
            ->layout('layouts.accountant.app', [
                'title' => "MailBox | School SaaS",
            ]);
    }
}
