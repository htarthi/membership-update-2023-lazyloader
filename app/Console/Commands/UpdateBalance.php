<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Models\SsOrder;
use Illuminate\Console\Command;

class UpdateBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This job will run every hour at the half-hour mark (12:30, 1:30, 2:30, etc…). Update order line items status pending to precessed.';

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
            logger('================ START:: UpdateBalance ==============');
            // logger('============= TIME:: ' . date('Y-m-d H:i:s') . '=============');
            $db_orders = SsOrder::where('tx_fee_status', 'pending')->get();
            foreach ($db_orders as $key => $value) {
                $shop = Shop::find($value->shop_id);
                if ($shop && @$value->is_test == 0) {
                    $shop->balance = ($shop->balance + $value->tx_fee_amount);
                    $shop->save();
                    $value->tx_fee_status = 'processed';
                    $value->save();
                }
            }
            logger('================ END:: UpdateBalance ==============');
        } catch (\Exception $e) {
            logger('================ ERROR:: UpdateBalance ==============');
            logger(json_encode($e));
        }
    }
}
