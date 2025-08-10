<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MomentoCommented extends Notification
{
    use Queueable;

    protected $commenter;
    protected $momentoId;

    public function __construct($commenter, $momentoId)
    {
        $this->commenter = $commenter;
        $this->momentoId = $momentoId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "{$this->commenter->username} comentou seu post.",
            'momento_id' => $this->momentoId,
        ];
    }
}
