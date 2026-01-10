<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientActivityDetected implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public string $message, public User $client)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.alerts'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'client_name' => $this->client->name,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
