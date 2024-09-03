<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\SsCancellation;
use App\Models\SsContract;
use App\Models\SsMetric;
use App\Models\SsOrder;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateShopMetrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $yesterday;
    protected $yesterdayStartOfTheDay;
    protected $yesterdayEndOfTheDay;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->yesterday = Carbon::yesterday()
            ->settings([
                'timezone', 'UTC',
            ]);
        $this->yesterdayStartOfTheDay = $this->yesterday->copy()->startOfDay();
        $this->yesterdayEndOfTheDay = $this->yesterday->endOfDay();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            logger('============== START:: CalculateShopMetrics ===========');
            logger('============== Yesterday Start :: ' . $this->yesterdayStartOfTheDay . ' ===========');
            logger('============== Yesterday End :: ' . $this->yesterdayEndOfTheDay . ' ===========');
            // Look for any shops that are currently installed
            // shops that have a row in the users table with password <> NULL
            // TODO: Check the relationship between shops and users
            $shops = Shop::with(['user' => function ($query) {
                $query->where('password', '<>', null);
            }])->get();

            foreach ($shops as $shop) {

                // Calculate active_subscriptions for each shop
                $active_subscriptions = SsContract::where([
                    ['shop_id', '=', $shop->id],
                    ['status', '=', 'active'],
                ])->get()->count();

                // Calculate paused_subscriptions for each shop
                $paused_subscriptions = SsContract::where([
                    ['shop_id', '=', $shop->id],
                    ['status', '=', 'paused'],
                ])->get()->count();

                // new_subscriptions
                // All contracts which were created between 00:00:00 and 23:59:59UTC the previous day
                $new_subscriptions = SsContract::where('shop_id', '=', $shop->id)
                    ->whereBetween('created_at', [
                        $this->yesterdayStartOfTheDay,
                        $this->yesterdayEndOfTheDay,
                    ])->get()->count();

                // orders_processed
                // All orders in which were created between 00:00:00 and 23:59:50UTC the previous day
                $orders_processed = SsOrder::where('shop_id', $shop->id)
                    ->whereBetween('created_at', [
                        $this->yesterdayStartOfTheDay,
                        $this->yesterdayEndOfTheDay,
                    ])->get();
                logger('============== Order Processes ===========');
                logger(json_encode($orders_processed));

                // amount_processed
                // Sum of all orders in ss_orders which were created between 00:00:00 and 23:59:59 the previous day.
                // in shop’s default currency
                $amount_processed = 0;
                foreach ($orders_processed as $order) {
                    if ($shop->currency != $order->order_currency) {
                        $amount_processed += calculateCurrency(
                            $order->order_currency,
                            $shop->currency,
                            $order->order_amount
                        );
                    } else {
                        $amount_processed += $order->order_amount;
                    }
                }

                // cacelledSubscriptions
                // All contracts which were cancelled between 00:00:00 and 23:59:59UTC the previous day
                $cacelledSubscriptions = SsCancellation::where('shop_id', '=', $shop->id)
                    ->whereBetween('created_at', [
                        $this->yesterdayStartOfTheDay,
                        $this->yesterdayEndOfTheDay,
                    ])->get()->count();
                SsMetric::create([
                    'shop_id' => $shop->id,
                    'shop_currency' => $shop->currency,
                    'date' => $this->yesterday,
                    'active_subscriptions' => $active_subscriptions,
                    'paused_subscriptions' => $paused_subscriptions,
                    'new_subscriptions' => $new_subscriptions,
                    'cancelled_subscriptions' => $cacelledSubscriptions ?? 0,
                    'orders_processed' => $orders_processed->count(),
                    'amount_processed' => $amount_processed ?? 0,
                ]);
            }
            logger('============== END:: CalculateMetrics ===========');
        } catch (\Exception $e) {
            logger('============== ERROR:: CalculateMetrics ===========');
            logger(json_encode($e));
            Bugsnag::notifyException($e);
        }
    }
}
