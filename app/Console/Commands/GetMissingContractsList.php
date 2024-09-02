<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Traits\ShopifyTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\sendMissingcontractslist;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class GetMissingContractsList extends Command
{
    use ShopifyTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:getMissingContractsList';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all contracts whose records are missing in DB';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        logger('============= START:: GetMissingContractsList =============');
        $currentHour = Carbon::now()->hour;
        $index = $currentHour % 4;
        $skips = [0, 150, 300, 450];
        $limit = 150;
        $hasBrokenContracts = false;
        $track = [];
        if ($index == 3) {
            $limit = 300;
        }
        $users = User::whereNull('deleted_at')->whereNotNull('plan_id')->where('is_working', true)->orderBy('id', 'desc')->skip($skips[$index])->take($limit)->get();
        if (count($users)) {
            foreach ($users as $user) {
                $hasMoreSubscriptionForToday = true;
                $nextCursor = null;
                $track[$user->id] = [
                    'name' => $user->name,
                    'contracts' => []
                ];
                while ($hasMoreSubscriptionForToday) {
                    if ($nextCursor) {
                        list($contracts, $pageInfo)  = $this->getContracts($user, $nextCursor = $nextCursor);
                    } else {
                        list($contracts, $pageInfo)  = $this->getContracts($user);
                    }
                    if (count($contracts)) {
                        foreach ($contracts as $contract) {
                            if ($contract) {
                                if ($pageInfo->hasNextPage) {
                                    $nextCursor = $pageInfo->endCursor;
                                }
                                if ($contract->createdAt < Carbon::now()->startOfDay()) {
                                    $hasMoreSubscriptionForToday = false;
                                    $nextCursor = null;
                                    // logger('=======> Last record of today of shop :: ' . $user->name);
                                    break;
                                }

                                $contractId = $this->checkContractIsRecordedInDB($user, $contract);
                                if ($contractId) {
                                    logger("============>Contract:: $contractId not created");
                                    $track[$user->id]['contracts'][] = $contractId;
                                    $hasBrokenContracts = true;
                                }
                            }
                        }
                    } else {
                        logger('=================> No Contracts found for user: ' . $user->name);
                        $hasMoreSubscriptionForToday = false;
                        $nextCursor = null;
                        logger('=======> Last record of today of shop :: ' . $user->name);
                    }
                }
            }
        }
        logger('============= END:: GetMissingContractsList =============');
        if ($hasBrokenContracts) {
            Mail::send(new sendMissingcontractslist($track));
        } else {
            logger('================> Yeah No any broken contracts... :)');
        }
        return true;
    }


    public function getContracts($user, $first = 100, $nextCursor = null)
    {
        logger('============= START:: getContracts =============');
        $queryTop = 'subscriptionContracts(first: ' . $first . ', reverse: true)';
        if ($nextCursor) {
            $queryTop = 'subscriptionContracts(first: ' . $first . ', reverse: true, after: "' . $nextCursor . '")';
        }
        $query = '{
            ' . $queryTop . '
             {
              nodes {
                createdAt
                id
                status
                updatedAt
                lastPaymentStatus
                billingPolicy {
                  interval
                  intervalCount
                  maxCycles
                  minCycles
                }
              }
              pageInfo {
                hasNextPage
                endCursor
                startCursor
              }
            }
          }';

        $result = $this->graphQLRequest($user->id, $query);
        if ($result['errors']) {
            logger(json_encode($result));
            return [[], []];
        }
        logger('============= END:: getContracts =============');
        return [$result['body']['data']['subscriptionContracts']['nodes'], $result['body']['data']['subscriptionContracts']['pageInfo']];
    }

    public function checkContractIsRecordedInDB($user, $contract)
    {
        logger('============= START:: checkContractIsRecordedInDB =============');
        $shop = Shop::where('user_id', $user->id)->select('id')->first();
        $admin_graphql_api_id = $contract['id'];
        $contractId = (int) str_replace('gid://shopify/SubscriptionContract/', '', $admin_graphql_api_id);
        $is_exist_db_contract = DB::table('ss_contracts')->where('shopify_contract_id', $contractId)->where('shop_id', $shop->id)->first();
        if (!$is_exist_db_contract) {
            return $contractId;
        } else {
            return null;
        }
        logger('============= END:: checkContractIsRecordedInDB =============');
    }
}
