<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Traits\GraphQLTrait;

class CheckShopAvailability extends Command
{
    use GraphQLTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:shopavailability';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to check shop is available or not in shopify, and set flag for that to user table';

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
        try {
            logger("============= START:: Check Shop availability ============");
            $users = User::orderBy('created_at', 'desc')->where('plan_id', '!=', null)->get();

            foreach ($users as $key => $user) {
                $result = $this->getShop($user);
                if ($result['errors']) {
                    $user->is_working = 0;
                    $user->is_working_response = json_encode($result);
                } else {
                    $user->is_working = 1;
                    $user->is_working_response = null;
                }
                $user->save();
            }
            logger("============= END:: Check Shop availability ============");
        } catch (\Exception $e) {
            logger("============= ERROR:: Check Shop availability ============");
            logger($e);
        }
        return 0;
    }

    public function getShop($user)
    {
        try {
            $parameter['fields'] = 'id';
            $query = '{
                  shop {
                    name
                    id
                  }
            }';
            $result = $user->api()->graph($query);

            // $result = $this->graph($user, $query, [], env('SHOPIFY_API_VERSION'));
            return $result;
        } catch (\Exception $e) {
            logger('================= ERROR:: getShop =================');
            logger($e->getMessage());
            return true;
        }
    }
}
