<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class CheckBillingAttemptFailure
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $ids = [];
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($webhookId, $userId, $shopId, $payloadJson)
    {
        logger('========== START EVENT:: CheckBillingAttemptFailure ==========');
        $this->ids['webhook_id'] = $webhookId;
        $this->ids['user_id'] = $userId;
        $this->ids['shop_id'] = $shopId;
        $this->ids['payload'] = $payloadJson;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
