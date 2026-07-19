<?php

namespace App\Http\Controllers\Api\Admin\Mailbox;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MailboxController extends Controller
{
    /* ------------------------------------------------------------------ */
    /*  LIST ENDPOINTS (plain array — no pagination, matches app pattern) */
    /* ------------------------------------------------------------------ */

    public function inbox(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $search = trim((string) $request->query('search', ''));

        $messages = Message::inbox($userId)
            ->with('sender:id,name,email')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('subject', 'like', "%{$search}%")
                       ->orWhere('body', 'like', "%{$search}%")
                       ->orWhereHas('sender', fn ($q3) => $q3->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Inbox messages fetched successfully',
            'data' => $messages,
            'unread_count' => Message::inbox($userId)->where('is_read', false)->count(),
        ]);
    }

    public function sent(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $search = trim((string) $request->query('search', ''));

        $messages = Message::sent($userId)
            ->with('receiver:id,name,email')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('subject', 'like', "%{$search}%")
                       ->orWhere('body', 'like', "%{$search}%")
                       ->orWhereHas('receiver', fn ($q3) => $q3->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Sent messages fetched successfully',
            'data' => $messages,
        ]);
    }

    public function important(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $search = trim((string) $request->query('search', ''));

        $messages = Message::important($userId)
            ->with(['sender:id,name,email', 'receiver:id,name,email'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('subject', 'like', "%{$search}%")
                       ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Important messages fetched successfully',
            'data' => $messages,
        ]);
    }

    public function trash(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $search = trim((string) $request->query('search', ''));

        $messages = Message::trash($userId)
            ->with(['sender:id,name,email', 'receiver:id,name,email'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('subject', 'like', "%{$search}%")
                       ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Trashed messages fetched successfully',
            'data' => $messages,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  SINGLE MESSAGE                                                    */
    /* ------------------------------------------------------------------ */

    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $folder = (string) $request->query('folder', 'inbox');

        $query = match ($folder) {
            'sent' => Message::sent($userId),
            'important' => Message::important($userId),
            'trash' => Message::trash($userId),
            default => Message::inbox($userId),
        };

        $message = $query->with(['sender:id,name,email', 'receiver:id,name,email'])->findOrFail($id);

        if (in_array($folder, ['inbox', 'important'], true) && $message->receiver_id === $userId) {
            $message->markAsRead();
        }

        return response()->json([
            'message' => 'Message fetched successfully',
            'data' => $message,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  RECIPIENT SEARCH (Compose "To" autocomplete)                      */
    /* ------------------------------------------------------------------ */

    public function searchUsers(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));

        if (mb_strlen($search) < 2) {
            return response()->json(['message' => 'Users fetched successfully', 'data' => []]);
        }

        $users = User::where('id', '!=', $request->user()->id)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(8)
            ->get(['id', 'name', 'email']);

        return response()->json([
            'message' => 'Users fetched successfully',
            'data' => $users,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  COMPOSE / REPLY                                                   */
    /* ------------------------------------------------------------------ */

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|integer|exists:users,id',
            'subject' => 'required|string|min:2|max:255',
            'body' => 'required|string|min:5',
        ], [
            'receiver_id.required' => 'Please select a recipient.',
            'receiver_id.exists' => 'Selected recipient not found.',
            'subject.required' => 'Subject is required.',
            'body.required' => 'Message body is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $sender = $request->user();

        $message = Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => (int) $request->input('receiver_id'),
            'subject' => (string) $request->input('subject'),
            'body' => (string) $request->input('body'),
        ]);

        // ── Notification (receiver ke pathabe) ─────────────────────────
        $receiver = User::find($message->receiver_id);
        if ($receiver) {
            NotificationService::send(
                $receiver,
                'message',
                'New Message',
                $sender->name . ' তোমাকে একটি message পাঠিয়েছে: ' . $message->subject,
                ['icon' => 'mail', 'url' => route('admin.mailbox.inbox')],
                'normal'
            );
        }

        // ── Activity Log ───────────────────────────────────────────────
        activity()
            ->causedBy($sender)
            ->performedOn($message)
            ->withProperties(['icon' => 'mail', 'type' => 'mailbox'])
            ->tap(function ($activity) use ($sender) {
                $activity->institution_id = $sender->institution_id;
            })
            ->log('Message sent to ' . ($receiver->name ?? 'user') . ': ' . $message->subject);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message->load(['sender:id,name,email', 'receiver:id,name,email']),
        ], 201);
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'body' => 'required|string|min:5',
        ], [
            'body.required' => 'Reply message is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = $request->user()->id;
        $original = Message::inbox($userId)->findOrFail($id);

        $reply = Message::create([
            'sender_id' => $userId,
            'receiver_id' => $original->sender_id,
            'subject' => 'Re: ' . $original->subject,
            'body' => (string) $request->input('body'),
        ]);

        // ── Notification (original sender-ke reply notify korbe) ────────
        $originalSender = User::find($original->sender_id);
        if ($originalSender) {
            NotificationService::send(
                $originalSender,
                'message',
                'New Reply',
                $request->user()->name . ' তোমার message-এর reply দিয়েছে: ' . $reply->subject,
                ['icon' => 'mail', 'url' => route('admin.mailbox.inbox')],
                'normal'
            );
        }

        return response()->json([
            'message' => 'Reply sent successfully',
            'data' => $reply->load(['sender:id,name,email', 'receiver:id,name,email']),
        ], 201);
    }

    /* ------------------------------------------------------------------ */
    /*  STATE CHANGES (important / trash / restore / delete)              */
    /* ------------------------------------------------------------------ */

    public function toggleImportant(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $message = Message::inbox($userId)->findOrFail($id);
        $message->update(['is_important' => ! $message->is_important]);

        return response()->json([
            'message' => 'Message updated successfully',
            'data' => $message,
        ]);
    }

    public function unmarkImportant(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $message = Message::important($userId)->findOrFail($id);
        $message->update(['is_important' => false]);

        return response()->json([
            'message' => 'Removed from important',
            'data' => $message,
        ]);
    }

    public function moveToTrash(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $folder = (string) $request->query('folder', 'inbox');

        if ($folder === 'sent') {
            $message = Message::sent($userId)->findOrFail($id);
            $message->update(['is_trashed_by_sender' => true]);
        } else {
            $message = Message::inbox($userId)->findOrFail($id);
            $message->update(['is_trashed_by_receiver' => true]);
        }

        return response()->json([
            'message' => 'Message moved to trash',
            'data' => $message,
        ]);
    }

    public function restore(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $message = Message::trash($userId)->findOrFail($id);

        if ($message->receiver_id === $userId) {
            $message->update(['is_trashed_by_receiver' => false]);
        } else {
            $message->update(['is_trashed_by_sender' => false]);
        }

        return response()->json([
            'message' => 'Message restored successfully',
            'data' => $message,
        ]);
    }

    public function permanentDelete(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $message = Message::trash($userId)->findOrFail($id);

        if ($message->receiver_id === $userId) {
            $message->update(['is_deleted_by_receiver' => true]);
        } else {
            $message->update(['is_deleted_by_sender' => true]);
        }

        return response()->json(['message' => 'Message permanently deleted']);
    }

    public function deleteSent(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $message = Message::sent($userId)->findOrFail($id);
        $message->update(['is_deleted_by_sender' => true]);

        return response()->json(['message' => 'Message deleted successfully']);
    }

    public function emptyTrash(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        foreach (Message::trash($userId)->get() as $message) {
            if ($message->receiver_id === $userId) {
                $message->update(['is_deleted_by_receiver' => true]);
            } else {
                $message->update(['is_deleted_by_sender' => true]);
            }
        }

        return response()->json(['message' => 'Trash emptied successfully']);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        return response()->json([
            'message' => 'Unread count fetched successfully',
            'data' => ['unread_count' => Message::inbox($userId)->where('is_read', false)->count()],
        ]);
    }
}