<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChargeClosingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public int $customer_id, public string $redraw_tag) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): Channel
    {
        return new Channel('charge_closing_channel');
    }

    /**
     * Get the name the event should be broadcast as.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'charge_closing.sent';
    }

    /**
     * Get the data to broadcast with the event.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return ['customer_id' => $this->customer_id, 'redraw_tag' => $this->redraw_tag];
    }
}
