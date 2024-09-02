<?php

namespace App\Jobs;

use App\Events\CheckBillingAttemptFailure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
class CheckBillingAttemptFailureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $webhookId;
    public $userId;
    public $shopId;
    public $payloadJson;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($webhookId, $userId, $shopId, $payloadJson)
    {
        $this->webhookId = $webhookId;
        $this->userId = $userId;
        $this->shopId = $shopId;
        $this->payloadJson = $payloadJson;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        event(new CheckBillingAttemptFailure($this->webhookId, $this->userId, $this->shopId, $this->payloadJson));
    }
}
