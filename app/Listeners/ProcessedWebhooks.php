<?php

namespace App\Listeners;

use App\Events\HandleWebhooks;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Traits\WebhookTrait;
class ProcessedWebhooks
{
    use WebhookTrait;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  HandleWebhooks  $event
     * @return void
     */
    public function handle(HandleWebhooks $event)
    {
        $request = $event->request;
        $this->webhookIndex($request);
    }
}
