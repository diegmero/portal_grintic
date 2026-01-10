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
        // For simplicity, we'll use a channel for the whole project or just the specific model.
        // Let's use: comments.{type}.{id}
        return [
            new PrivateChannel('comments.' . $this->comment->commentable_type . '.' . $this->comment->commentable_id),
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
}
