<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Comment $comment)
    {
    }

    public function broadcastOn(): array
    {
        // Broadcast to the specific task/stage channel. 
        // Sanitize type name: replace backslashes with dots (Pusher doesn't allow backslashes)
        $sanitizedType = str_replace('\\', '.', $this->comment->commentable_type);
        return [
            new PrivateChannel('comments.' . $sanitizedType . '.' . $this->comment->commentable_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->comment->id,
            'body' => $this->comment->body,
            'user' => [
                'id' => $this->comment->user->id,
                'name' => $this->comment->user->name,
            ],
            'created_at' => $this->comment->created_at->toIso8601String(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'CommentCreated';
    }
}
