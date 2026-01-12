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

class CommentUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Comment $comment)
    {
    }

    public function broadcastOn(): array
    {
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
            'updated_at' => $this->comment->updated_at->toIso8601String(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CommentUpdated';
    }
}
