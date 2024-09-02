<?php

namespace App\Observers;

use App\Models\Install;
use App\Models\Shop;
use App\Models\SsSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Osiset\ShopifyApp\Storage\Models\Charge;
use Osiset\ShopifyApp\Storage\Models\Plan;

class UserObserver
{
    public function saving(User $user)
    {
        logger('============ START :: user observer ============');
        Auth::setUser($user);
        $old_plan = $user->getOriginal('plan_id');
        $new_plan = $user->plan_id;
        // logger('============ START :: user observer ============' . $new_plan);
        $free_plan = Plan::where('is_free_trial_plans', 1)->first();
        Auth::loginUsingId($user->id);
        if ($old_plan != $new_plan) {
            $plan = Plan::find($user->plan_id);

            $shop = Shop::where('user_id', $user->id)->first();
            $setting = SsSetting::where('shop_id', $shop->id)->first();
            $setting->billing_plan_id = $user->plan_id;
            $setting->transaction_fee = ($plan) ? $plan->transaction_fee : 0.0015;
            $setting->member_fee = ($plan) ? $plan->transaction_fee : 0.0015;
            $setting->save();




            if ($new_plan !== $free_plan->id) {

                if (($old_plan == '' || is_null($old_plan))) {

                    // update terms in charge table
                    $charge = Charge::where('status', 'ACTIVE')->where('user_id', $shop->user_id)->orderBy('created_at', 'desc')->first();
                    if($charge){
                        $charge->terms =  $plan->terms  ? $plan->terms : '';
                        $charge->save();
                    }
                    if (env('APP_ENV') == 'production') {
                        $header = 'Content-Type: application/json';
                        $url = 'https://maker.ifttt.com/trigger/notification/with/key/kUcdtO7EJKsd4COR7AcoJmZbRyVu3hkrP5t22xO91GW';
                        $data = array(
                            'value1' => 'New Install',
                            'value2' => $shop->name . ' installed with ' . $plan->name . ' plan',
                            'value3' => $shop->domain
                        );
                        triggerCURL($data, $header, $url);
                    }
                }
            }
        }
        logger('============ END :: user observer ============');
    }
}
