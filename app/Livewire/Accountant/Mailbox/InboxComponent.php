<?php

namespace App\Livewire\Accountant\Mailbox;

use App\Models\Message;
use Livewire\Component;
use Livewire\WithPagination;

class InboxComponent extends Component
{
    use WithPagination;

    public ?int    $viewingId    = null;
    public ?Message $viewing     = null;
    public string  $search       = '';
    public string  $replyBody    = '';
    public bool    $showReply    = false;

    protected $queryString = ['search'];

    /* ------------------------------------------------------------------ */

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /* ------------------------------------------------------------------ */

    public function viewMessage(int $id): void
    {
        $message = Message::inbox(auth()->id())->with('sender')->findOrFail($id);
        $message->markAsRead();

        $this->viewing    = $message;
        $this->viewingId  = $id;
        $this->showReply  = false;
        $this->replyBody  = '';
    }

    public function backToList(): void
    {
        $this->viewing   = null;
        $this->viewingId = null;
        $this->showReply = false;
        $this->replyBody = '';
    }

    /* ------------------------------------------------------------------ */

    public function toggleImportant(int $id): void
    {
        $message = Message::inbox(auth()->id())->findOrFail($id);
        $message->update(['is_important' => ! $message->is_important]);

        // Refresh viewing message if open
        if ($this->viewingId === $id) {
            $this->viewing->is_important = ! $this->viewing->is_important;
        }

        $this->dispatch('notify', type: 'success', msg: 'Updated!');
    }

    public function trashMessage(int $id): void
    {
        Message::inbox(auth()->id())->findOrFail($id)
               ->update(['is_trashed_by_receiver' => true]);

        if ($this->viewingId === $id) {
            $this->backToList();
        }

        $this->dispatch('notify', type: 'warning', msg: 'Moved to trash.');
    }

    /* ------------------------------------------------------------------ */

    public function sendReply(): void
    {
        $this->validate(['replyBody' => 'required|min:5']);

        $original = $this->viewing;

        Message::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $original->sender_id,
            'subject'     => 'Re: ' . $original->subject,
            'body'        => $this->replyBody,
        ]);

        $this->replyBody  = '';
        $this->showReply  = false;
        $this->dispatch('notify', type: 'success', msg: 'Reply sent!');
    }

    /* ------------------------------------------------------------------ */

    public function render()
    {
        $userId = auth()->id();

        $messages = Message::inbox($userId)
            ->with('sender')
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('subject', 'like', "%{$this->search}%")
                       ->orWhere('body', 'like', "%{$this->search}%")
                       ->orWhereHas('sender', fn ($q3) =>
                           $q3->where('name', 'like', "%{$this->search}%")
                       )
                )
            )
            ->latest()
            ->paginate(15);

        $unreadCount = Message::inbox($userId)->where('is_read', false)->count();

        return view('livewire.accountant.mailbox.inbox-component', compact('messages', 'unreadCount'))
            ->layout('layouts.accountant.app', [
                'title' => "MailBox | School SaaS",
            ]);
    }
}
