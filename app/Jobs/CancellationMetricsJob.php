<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\SsCancellation;
use App\Models\SsMetric;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
class CancellationMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $startTime = null;
    public $endTime = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            logger('================== START :: CancellationMetricsJob ========================');
            $this->startTime = Carbon::createFromFormat('d/m/Y H:i:s', '01/09/2022 00:00:00')->settings([
                'timezone', 'UTC',
            ]);
            $this->endTime = $this->startTime->copy()->endOfDay();
            $shops = Shop::with(['user' => function ($query) {
                $query->where('password', '<>', null);
            }])->get();
            ini_set('xdebug.max_nesting_level', 9999);
            $this->recordsWithDate($shops);
            logger('================== END :: CancellationMetricsJob ========================');
        } catch (\Throwable $th) {
            logger('================== CancellationMetricsJob::Error ========================');
            logger($th->getMessage());
        }
    }

    public function recordsWithDate($shops)
    {
        foreach ($shops as $shop) {
            $record = SsMetric::where('shop_id', '=', $shop->id)
                ->where('cancelled_subscriptions', null)
                ->whereBetween('created_at', [
                    $this->startTime,
                    $this->endTime,
                ])
                ->first();
            if ($record) {
                $cacelledSubscriptions = SsCancellation::where('shop_id', '=', $shop->id)
                    ->whereBetween('created_at', [
                        $this->startTime,
                        $this->endTime,
                    ])
                    ->count();
                if ($cacelledSubscriptions > 0) {
                    $record->cancelled_subscriptions = $cacelledSubscriptions;
                    logger("----------------> Cancellation found shop_id :: $shop->id , Edited Metric :: $record->id , cacelledSubscriptionsCount :: $cacelledSubscriptions <--------------");
                } else {
                    $record->cancelled_subscriptions = 0;
                }
                $record->save();
            }
        }
        $this->startTime = $this->startTime->copy()->addDay();
        $this->endTime = $this->startTime->copy()->endOfDay();

        if (Carbon::parse('2022-10-31 00:00:00')->greaterThanOrEqualTo($this->startTime)) {
            $this->recordsWithDate($shops);
        } else {
            $this->startTime = Carbon::createFromFormat('d/m/Y H:i:s', '01/09/2022 00:00:00')->settings([
                'timezone', 'UTC',
            ]);
            $this->endTime = $this->startTime->copy()->endOfDay();
        }
    }
}
