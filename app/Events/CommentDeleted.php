<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public $commentId, public $commentableType, public $commentableId)
    {
    }

    public function broadcastOn(): array
    {
        $sanitizedType = str_replace('\\', '.', $this->commentableType);
        return [
            new PrivateChannel('comments.' . $sanitizedType . '.' . $this->commentableId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->commentId,
        ];
    }

    public function broadcastAs(): string
    {
        return 'CommentDeleted';
    }
}
