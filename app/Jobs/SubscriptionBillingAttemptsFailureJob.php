<?php namespace App\Jobs;

use App\Events\CheckBillingAttemptFailure;
use App\Models\Shop;
use App\Traits\ShopifyTrait;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Osiset\ShopifyApp\Contracts\Objects\Values\ShopDomain;
use stdClass;

class SubscriptionBillingAttemptsFailureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ShopifyTrait;
    /**
     * Shop's myshopify domain
     *
     * @var ShopDomain
     */
    public $shopDomain;

    /**
     * The webhook data
     *
     * @var object
     */
    public $data;

    /**
     * Create a new job instance.
     *
     * @param string   $shopDomain The shop's myshopify domain
     * @param stdClass $data    The webhook data (JSON decoded)
     *
     * @return void
     */
    public function __construct($shopDomain, $data)
    {
        $this->shopDomain = $shopDomain;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            logger('============== START:: Subscription Billing Attempts Failure Webhook ===========');
            $domain = $this->shopDomain->toNative();

            $user = User::where('name', $domain)->first();
            $shop = Shop::where('domain', $domain)->where('user_id', $user['id'])->first();

            $webhookId = $this->webhook('subscription_billing_attempts/failure', $user->id, json_encode($this->data));

            event(new CheckBillingAttemptFailure($webhookId, $user->id, $shop->id));
        }catch ( \Exception $e ){
            Bugsnag::notifyException($e);
        }
        return response()->json(['data' => 'success'], 200);
    }
}
