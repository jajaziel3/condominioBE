<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $user;
    public string $message;
    public string $timestamp;

    public function __construct(string $user, string $message)
    {
        $this->user = $user;
        $this->message = $message;
        $this->timestamp = now()->toISOString();
    }

    public function broadcastOn(): Channel
    {
        return new Channel('chat');
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'message',
            'user' => $this->user,
            'content' => $this->message,
            'timestamp' => $this->timestamp,
        ];
    }
}