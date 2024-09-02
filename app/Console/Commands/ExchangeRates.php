<?php

namespace App\Console\Commands;

use App\Constants\Api;
use App\Models\ExchangeRate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ExchangeRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange:rates';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the latest exchange rates and add a row in the database';
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
        logger('============= START:: ExchangeRates =============');
        // logger('============= TIME:: ' . date('Y-m-d H:i:s') . '=============');
        try {
            $endPoint = Api::EXCHANGE_RATE_BASE_URL . 'api/latest.json?app_id=' . env('EXCHANGE_RATE_API_KEY');
            $response = Http::get($endPoint);
            if ($response->successful()) {
                $result = $response->json();
                $rates = $result['rates'];
                $exchange_rate =  new ExchangeRate;
                $exchange_rate->conversion_rates = json_encode($rates);
                $exchange_rate->save();
            }
            logger('============= END:: ExchangeRates =============');
        } catch (\Exception $e) {
            logger('============= ERROR:: ExchangeRates =============');
            logger(json_encode($e));
        }
        return 0;
    }
}
