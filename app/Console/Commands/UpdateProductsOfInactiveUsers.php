<?php

namespace App\Console\Commands;

use App\Models\SsPlanGroupVariant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Jobs\UpdateProductData;
class UpdateProductsOfInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:updateProductsOfInactiveUsers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will check for inactive users and update the products of subscription plans';

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
        logger('================ START:: UpdateProductsOfInactiveUsers ==============');
        $checkDays = 2;
        $date = Carbon::now()->subDays($checkDays)->startOfDay();
        $users = User::whereNull('deleted_at')->where('plan_id', '!=', null)->where('is_working', true)->where('last_login', '<', $date)->orWhereNull('last_login')->get();
        foreach ($users as $key => $user) {
            $products = SsPlanGroupVariant::where('user_id', $user->id)->whereNull('deleted_at')->get();
            foreach ($products as $product) {
                dispatch(new UpdateProductData($user, $product))->onQueue('UpdateServer');
            }
            $user->last_login = Carbon::now();
            $user->save();
        }
        logger('================ END:: UpdateProductsOfInactiveUsers ==============');
        return 0;
    }
}
