<?php

namespace App\Console;

use App\Jobs\CalculateShopMetrics;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        to create usage charge for extra members
         // $schedule->command('usage:charge')->dailyAt('23:00')->timezone('UTC');

//         to reset billing on date in charge table
         $schedule->command('track:charge')->dailyAt('12:12')->timezone('UTC');

//         to reset member count in shop table
         // $schedule->command('reset:membercount')->dailyAt('12:30')->timezone('UTC');
          $schedule->command('reset:membercount')->dailyAt('23:00')->timezone('UTC');

         if( env('APP_ENV') == 'production' ){
             $schedule->command('exchange:rates')->everySixHours(5);
         }else{
             $schedule->command('exchange:rates')->daily();
         }

         $schedule->command('update:balance')->hourlyAt(30)->between('1:00', '22:00');
//          $schedule->command('exchange:rates')->cron('0 */1 * * *');

//        to create recurring order
        $schedule->command('billing:attempt')->everyTenMinutes();
        // $schedule->command('billing:attempt')->everySecond();

        // Dispatch the job for calculating installed shops' metrics
        $schedule->job(new CalculateShopMetrics)->dailyAt('00:15')->timezone('UTC');

//        run cron job to handle cancel membership
        $schedule->command('handle:cancelmembership')->everySixHours(25)->between('1:00', '23:00');

//       Run cron to check shop availability
        $schedule->command('check:shopavailability')->dailyAt('13:15')->timezone('UTC');

        $schedule->command('remove:oldwebhooks')->dailyAt('07:00')->timezone('UTC');

//      Run job to check recurring notify
        $schedule->command('recurring:notify')->dailyAt('4:38')->timezone('UTC');
        $schedule->command('recurring:notifyAdvanceMailGun')->dailyAt('4:05')->timezone('UTC');
        $schedule->command('app:track-store-credit-transaction')->everySixHours(23);

        // Schedule for missing contracts
        $schedule->command('command:getMissingContractsList')->hourly(45)->timezone('UTC');
        $schedule->command('cron:updateProductsOfInactiveUsers')->dailyAt('6:38');

        $schedule->command('missing-billing-attempt')->everyThreeHours(35)->timezone('UTC');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
