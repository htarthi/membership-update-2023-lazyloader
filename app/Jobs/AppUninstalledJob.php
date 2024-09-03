<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\SsActivityLog;
use App\Models\SsContract;
use App\Models\SsContractLineItem;
use App\Models\SsDeletedProduct;
use App\Models\SsEmail;
use App\Models\SsEvents;
use App\Models\SsFailedPayment;
use App\Models\SsOrder;
use App\Models\SsPlanGroup;
use App\Models\SsPlanGroupVariant;
use App\Models\SsSetting;
use App\Models\SsTwilio;
use App\Models\SsTwilioBlacklist;
use App\Models\SsWebhook;
use App\Models\User;
use App\Traits\ShopifyTrait;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Osiset\ShopifyApp\Actions\CancelCurrentPlan;
use Osiset\ShopifyApp\Contracts\Commands\Shop as IShopCommand;
use Osiset\ShopifyApp\Contracts\Objects\Values\ShopDomain;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;
use stdClass;
class AppUninstalledJob extends \Osiset\ShopifyApp\Messaging\Jobs\AppUninstalledJob
{
    use ShopifyTrait;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
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
    public function handle(
        IShopCommand $shopCommand,
        IShopQuery $shopQuery,
        CancelCurrentPlan $cancelCurrentPlanAction
    ): bool {
        logger('=============== AppUninstalledJob =============');

        $domain = $this->shopDomain->toNative();
        $us = User::where('name', $domain)->first();
        $user = $shopQuery->getByDomain($this->shopDomain);
        $shopId = $user->getId();
        $us->active = 0;
        $us->save();
        // Cancel the current plan
        $cancelCurrentPlanAction($shopId);
        // Purge shop of token, plan, etc.
        $shopCommand->clean($shopId);
        // Soft delete the shop.
        $shopCommand->softDelete($shopId);
        return true;
    }

    public function deleteRecords($userId)
    {
        try {
            logger('=============== START :: deleteRecords FROM AppUninstalledJob =============');
            $shop = Shop::where('user_id', $userId)->first();
            $this->event($shop->user_id, 'Install', 'App Uninstalled', 'Merchant uninstalled the app');
            SsWebhook::where('user_id', $userId)->delete();
            SsTwilioBlacklist::where('shop_id', $shop->id)->delete();
            SsTwilio::where('shop_id', $shop->id)->delete();
            SsSetting::where('shop_id', $shop->id)->delete();
            SsPlanGroup::where('shop_id', $shop->id)->delete();
            SsPlanGroupVariant::where('shop_id', $shop->id)->delete();
            SsOrder::where('shop_id', $shop->id)->delete();
            SsFailedPayment::where('shop_id', $shop->id)->delete();
            SsEmail::where('shop_id', $shop->id)->delete();
            SsEvents::where('shop_id', $shop->id)->delete();
            SsContractLineItem::where('user_id', $userId)->delete();
            SsContract::where('user_id', $userId)->delete();
            SsActivityLog::where('user_id', $userId)->delete();
            SsDeletedProduct::where('user_id', $userId)->delete();
            Shop::where('user_id', $userId)->delete();
            logger('=============== END :: deleteRecords FROM AppUninstalledJob =============');
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
        }
    }
}
