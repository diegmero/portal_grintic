<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CommentActivityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $action; // 'updated' or 'deleted'
    public $sender;
    public $context;
    public $content;

    public function __construct($action, $sender, $context, $content = '')
    {
        $this->action = $action;
        $this->sender = $sender;
        $this->context = $context;
        $this->content = $content;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'message' => $this->buildMessage($notifiable),
            'type' => 'comment_' . $this->action,
            'link' => '#',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        $titles = [
            'updated' => '✏️ Comentario Editado',
            'deleted' => '🗑️ Comentario Eliminado',
        ];

        return new BroadcastMessage([
            'message' => $this->buildMessage($notifiable),
            'title' => $titles[$this->action] ?? 'Actividad',
            'type' => 'info',
        ]);
    }

    protected function buildMessage($notifiable)
    {
        $typeLabel = $this->context['type_label'];
        $contextName = $this->context['name'];
        $senderName = $this->sender->name;
        
        // Content Preview
        $preview = '';
        if ($this->content) {
            $preview = strlen($this->content) > 50 ? substr($this->content, 0, 50) . '...' : $this->content;
            $preview = ": \"{$preview}\"";
        }

        $actionText = $this->action === 'updated' ? 'editó un comentario' : 'eliminó un comentario';

        if ($this->sender->company_id) {
            // Client Action -> Admin Recipient
            return "El cliente {$senderName} {$actionText} en {$typeLabel} \"{$contextName}\"{$preview}";
        } else {
            // Admin Action -> Client Recipient
            return "Admin {$actionText} en {$typeLabel} \"{$contextName}\"{$preview}";
        }
    }
}
