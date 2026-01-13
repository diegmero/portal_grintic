<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $comment;
    public $sender;
    public $context;

    public function __construct($comment, $sender, $context)
    {
        $this->comment = $comment;
        $this->sender = $sender;
        $this->context = $context;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'message' => $this->buildMessage($notifiable),
            'type' => 'comment_created',
            'link' => '#', // TODO: Add link to context
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => $this->buildMessage($notifiable),
            'title' => '💬 Nuevo Comentario',
            'client_name' => $this->sender->name, // For consistency with frontend expectation
            'type' => 'info',
        ]);
    }

    protected function buildMessage($notifiable)
    {
        $typeLabel = $this->context['type_label'];
        $contextName = $this->context['name'];
        $body = $this->comment->body;
        
        // Truncate body if too long
        $preview = strlen($body) > 50 ? substr($body, 0, 50) . '...' : $body;

        if ($this->sender->company_id) {
            // Sender is Client -> Recipient is Admin
            return "El cliente {$this->sender->name} comentó en {$typeLabel} \"{$contextName}\": \"{$preview}\"";
        } else {
            // Sender is Admin -> Recipient is Client
            return "Tienes un nuevo comentario de Admin en {$typeLabel} \"{$contextName}\": \"{$preview}\"";
        }
    }
}
