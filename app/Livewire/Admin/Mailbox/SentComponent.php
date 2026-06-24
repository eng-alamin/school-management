<?php

namespace App\Livewire\Admin\Mailbox;

use App\Models\Message;
use Livewire\Component;
use Livewire\WithPagination;

class SentComponent extends Component
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
        $this->viewing   = Message::sent(auth()->id())->with('receiver')->findOrFail($id);
        $this->viewingId = $id;
    }

    public function backToList(): void
    {
        $this->viewing   = null;
        $this->viewingId = null;
    }

    /* ------------------------------------------------------------------ */

    public function trashMessage(int $id): void
    {
        Message::sent(auth()->id())->findOrFail($id)
               ->update(['is_trashed_by_sender' => true]);

        if ($this->viewingId === $id) {
            $this->backToList();
        }

        $this->dispatch('notify', type: 'warning', msg: 'Moved to trash.');
    }

    public function deleteMessage(int $id): void
    {
        Message::sent(auth()->id())->findOrFail($id)
               ->update(['is_deleted_by_sender' => true]);

        if ($this->viewingId === $id) {
            $this->backToList();
        }

        $this->dispatch('notify', type: 'error', msg: 'Message deleted.');
    }

    /* ------------------------------------------------------------------ */

    public function render()
    {
        $messages = Message::sent(auth()->id())
            ->with('receiver')
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('subject', 'like', "%{$this->search}%")
                       ->orWhere('body', 'like', "%{$this->search}%")
                       ->orWhereHas('receiver', fn ($q3) =>
                           $q3->where('name', 'like', "%{$this->search}%")
                       )
                )
            )
            ->latest()
            ->paginate(15);

        return view('livewire.admin.mailbox.sent-component', compact('messages'))
            ->layout('layouts.admin.app', [
                'title' => 'MailBox | ' . institution()->name,
            ]);
    }
}
