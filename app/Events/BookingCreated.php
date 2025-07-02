<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $bookingId;
    public $investorId;

    public function __construct($message, $investorId, $bookingId)
    {
        $this->message = $message;
        $this->investorId = $investorId;
        $this->bookingId = $bookingId;
    }

    public function broadcastOn()
    {
        return ['my-channel'];
        // return new Channel('my-channel'.$this->investorId);
    }

    public function broadcastAs()
    {
        return 'my-event';
        // return 'booking-notification';
    }
}
