<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
class RecurringNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email Notification - notify customers when they have a recurring payment coming up';

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
            logger("============= START:: RecurringNotify ============");
            $users = User::select('users.id AS user_id', 'shops.id AS shop_id', 'ss_emails.days_ahead', 'ss_emails.html_body', 'ss_emails.subject', 'ss_contracts.next_order_date', 'ss_contracts.shopify_contract_id', 'shops.iana_timezone', 'ss_settings.recurring_notify_email_enabled', 'ss_settings.email_from_email', 'ss_settings.email_from_name', 'ss_customers.email', 'ss_customers.id AS customer_id')->where(['users.active' => 1, 'users.is_working' => 1,'users.plan_id','!=', null])
                ->join('shops', 'shops.user_id', '=', 'users.id')
                ->join('ss_emails', 'ss_emails.shop_id', '=', 'shops.id')
                ->join('ss_settings', 'ss_settings.shop_id', '=', 'shops.id')
                ->join('ss_contracts', 'ss_contracts.shop_id', 'shops.id')
                ->join('ss_customers', 'ss_customers.id', 'ss_contracts.ss_customer_id')
                ->where('ss_contracts.status', 'active')
                ->where('ss_emails.category', 'recurring_notify')
                ->where('ss_settings.recurring_notify_email_enabled', 1)
                ->where('ss_settings.mailgun_method', '!=', "Advanced")
                ->get();

            foreach ($users as $key => $user) {
                $default_timezone = date_default_timezone_get();
                date_default_timezone_set($user->iana_timezone);
                $currDate = date('Y-m-d H:i:s');

                $from = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $currDate);
                $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $user->next_order_date);
                $diff_in_days = $to->diffInDays($from);

                if ($diff_in_days == $user->days_ahead) {
                    // logger("customer :: " . $user->customer_id);
                    $planData = [];
                    $planData['renewal_date'] = date('d/m/Y', strtotime($user->next_order_date));
                    $res = sendMailH($user->subject, $user->html_body, $user->email_from_email, $user->email, $user->email_from_name, $user->shop_id, $user->customer_id, $planData);
                }
                date_default_timezone_set($default_timezone);
            }
            logger("============= END:: RecurringNotify ============");
        } catch (\Exception $e) {
            logger("============== ERROR:: RecurringNotify ===========");
            logger($e);
        }
        return 0;
    }
}
