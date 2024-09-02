<?php

namespace App\Console\Commands;

use App\Models\SsStoreCredit;
use Illuminate\Console\Command;
use App\Models\User;
use Gnikyt\BasicShopifyAPI\BasicShopifyAPI;
use Gnikyt\BasicShopifyAPI\Options;
use Gnikyt\BasicShopifyAPI\Session;
use App\Models\Shop;
class TrackStoreCreditTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:track-store-credit-transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        logger('============= START:: TrackStoreCreditTransaction =============');
        try {
            $store_credit = SsStoreCredit::get();
            if (!empty($store_credit)) {
                foreach ($store_credit as $credit) {
                    if (isset($credit->shopify_storecreditaccount_id)) {
                        $query = 'query {
                          storeCreditAccount(id: "gid://shopify/StoreCreditAccount/' . $credit->shopify_storecreditaccount_id . '") {
                            id
                            balance {
                              amount
                              currencyCode
                            }
                          }
                        }';
                        $parameters = [];
                        $version = 'unstable';
                        $user = Shop::find($credit->shop_id);
                        $store_shop = User::find($user->user_id);
                        $options = new Options();
                        $options->setVersion($version);
                        $api = new BasicShopifyAPI($options);
                        $api->setSession(new Session(
                            $store_shop->name,
                            $store_shop->password
                        ));
                        $result = $api->graph($query, $parameters);
                        if (!$result['errors']) {
                            $balance = $result['body']['container']['data']['storeCreditAccount'] ? $result['body']['container']['data']['storeCreditAccount']['balance']['amount'] : 0;
                            $credit->balance = $balance;
                            $credit->save();
                        }
                    }
                }
            }
            logger('============= END:: TrackStoreCreditTransaction =============');
        } catch (\Exception $e) {
            logger(json_encode($e));
            return $e->getMessage();
            logger('============= ERROR:: TrackStoreCreditTransaction =============');
        }
    }
}
