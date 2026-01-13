<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseClosingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public int $supplier_id, public string $redraw_tag) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): Channel
    {
        return new Channel('purchase_closing_channel');
    }

    /**
     * Get the name the event should be broadcast as.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'purchase_closing.sent';
    }

    /**
     * Get the data to broadcast with the event.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return ['supplier_id' => $this->supplier_id, 'redraw_tag' => $this->redraw_tag];
    }
}
