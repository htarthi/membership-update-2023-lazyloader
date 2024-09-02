<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlanRequest;
use App\Http\Requests\SettingRequest;
use App\Models\SsEmail;
use App\Models\SsSetting;
use App\Traits\ImageTrait;
use App\Traits\ShopifyTrait;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Log;
use Osiset\ShopifyApp\Storage\Models\Charge;
use Osiset\ShopifyApp\Storage\Models\Plan;
use App\Models\SsCancellationReason;

class SettingController extends Controller
{
    use ShopifyTrait;
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $shopID = get_shopID_H();
            $shop = getShopH();
            $user = User::find($shop->user_id);
            $metafields = getShopMetaFields();

            $setting = SsSetting::where('shop_id', $shopID)->first();

            $failedPaymentNoti = SsEmail::where('shop_id', $shopID)->where('category', 'failed_payment_to_customer')->first();
            $failedPaymentNoti = ($failedPaymentNoti) ?: $this->addEmailTemplate('failed_payment_to_customer', $shop);

            $newSubNoti = SsEmail::where('shop_id', $shopID)->where('category', 'new_membership_to_customer')->first();
            $newSubNoti = ($newSubNoti) ?: $this->addEmailTemplate('new_membership_to_customer', $shop);

            $cancelMembershipNoti = SsEmail::where('shop_id', $shopID)->where('category', 'cancelled_membership')->first();
            $cancelMembershipNoti = ($cancelMembershipNoti) ?: $this->addEmailTemplate('cancelled_membership', $shop);

            $recurringNotifyEmailNoti = SsEmail::where('shop_id', $shopID)->where('category', 'recurring_notify')->first();
            $recurringNotifyEmailNoti = ($recurringNotifyEmailNoti) ?: $this->addEmailTemplate('recurring_notify', $shop);

            $charge = Charge::where('user_id', $shop->user_id)->where('status', 'ACTIVE')->orderBy('created_at', 'desc')->first();

            $plans = Plan::select('name', 'price', 'transaction_fee','is_free_trial_plans')->get()->toArray();

            $portal = SsSetting::where('shop_id', $shopID)->select(['portal_can_cancel'])->first()->toArray();

            $curr_date = date('Y-m-d H:i:s');


            $trial_end_date = ($charge) ? date("Y-m-d H:i:s", strtotime($charge->trial_ends_on)) : '';

            // $new = array_column($metafields, 'value', 'key');
            foreach ($metafields as $key => $field) {
                $settingKey = $field['key'];
                $metaIDKey = $field['key'] . '_mi';
                $setting[$settingKey] = $field['value'];
                $setting[$metaIDKey] = $field['id'];
            }

            $freePlans = false;
            if ($user->plan_id) {
                $getPlan = Plan::where('id', $user->plan_id)->first();
                if ($getPlan && $getPlan->is_free_trial_plans) {
                    $freePlans = true;
                }
            } else {
                $freePlans = true;
            }



            $check_reason_exist = SsCancellationReason::where('shop_id', $shopID)->count();
            if($check_reason_exist == 0){
                $descriptions = [
                    'Technical issues',
                    'Confusing/I couldn’t figure it out',
                    'Too expensive',
                    'I didn’t like the features'
                ];
                foreach ($descriptions as $description) {
                    $cancel_reason = new SsCancellationReason;
                    $cancel_reason->shop_id = $shopID;
                    $cancel_reason->reason = $description;
                    $cancel_reason->save();
                }
            }
            $getReason =  SsCancellationReason::where('shop_id',$shopID)->get();
            $setting->reasons = $getReason ? $getReason : '';

            $plan = Plan::where('id', $user->plan_id)->first();
            $data['setting'] = $setting;

            $data['failedPaymentNoti'] = $failedPaymentNoti;
            $data['newSubNoti'] = $newSubNoti;
            $data['cancelMembershipNoti'] = $cancelMembershipNoti;
            $data['recurringNotifyEmailNoti'] = $recurringNotifyEmailNoti;

            $data['timezone'] = $shop->iana_timezone;
            $data['portal'] = $portal;
            $data['plan']['active_plan_id'] = $user->plan_id;
            $data['plan']['active_plan_name'] = $plan ? $plan->name : null;
            $data['plan']['trial_ends'] = ($curr_date > $trial_end_date || $trial_end_date == '') ? '' : strtoupper(date("M d, Y", strtotime($charge->trial_ends_on)));
            $data['plan']['current_txn_fee'] = number_format($setting->member_fee * 100, 2);;
            $data['plans'] = $plans;
            $data['plan']['price'] = @$charge->price;
            $data['user']['id'] = $user->id;
            $data['user']['name'] = $user->name;
            $data['plan']['freePlans'] = $freePlans;


            $response['data'] = $data;
            $response['themes'] = $this->getThemes();

            return response()->json(['data' => $response], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  index =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePortal(Request $request)
    {
        try {
            $shopID = get_shopID_H();
            $key = $request->key;
            $customerP = SsSetting::where('shop_id', $shopID)->first();
            $customerP->$key = $request->v;
            $customerP->save();

            return response()->json(['data' => 'Status Changed successfully'], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  changePortal =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * store settings
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(SettingRequest $request)
    {
        try {
            DB::beginTransaction();
            $shopID = get_shopID_H();
            $shop = getShopH();
            //            save settings
            $data = $request->data['setting'];

            $setting = SsSetting::where('shop_id', $shopID)->first();

            $setting->dunning_retries = $data['dunning_retries'];
            $setting->dunning_daysbetween = $data['dunning_daysbetween'];
            $setting->dunning_failedaction = $data['dunning_failedaction'];

            if (($setting->dunning_email_enabled == 0) && ($data['dunning_email_enabled'])) {
                $this->event($shop->user_id, 'Onboarding', 'Dunning Email Enabled', '
                                Merchant enabled their dunning email');
            }
            $setting->dunning_email_enabled = $data['dunning_email_enabled'];
            $setting->new_subscription_email_enabled = $data['new_subscription_email_enabled'];
            $setting->membership_cancel_email_enabled = $data['membership_cancel_email_enabled'];
            $setting->recurring_notify_email_enabled = $data['recurring_notify_email_enabled'];
            $setting->email_from_name = $data['email_from_name'];
            $setting->email_from_email = $data['email_from_email'];
            $setting->subscription_daily_at = $data['subscription_daily_at'];
            $setting->restricted_content = $data['restricted_content'];
            $setting->auto_fulfill = $data['auto_fulfill'];

            $setting->notify_email = $data['notify_email'];
            $setting->notify_new = $data['notify_new'];
            $setting->notify_cancel = $data['notify_cancel'];
            $setting->notify_revoke = $data['notify_revoke'];
            $setting->notify_paymentfailed = $data['notify_paymentfailed'];
            $setting->portal_can_cancel = $data['portal_can_cancel'];

            $setting->send_account_invites = $data['send_account_invites'];

            $setting->mailgun_method = $data['mailgun_method'];

            $setting->cancellation_reason_enable = $data['cancellation_reason_enable'];
            $setting->cancellation_reason_enable_custom = $data['cancellation_reason_enable_custom'];
            $setting->custom_reason_message = $data['custom_reason_message'];
            $setting->custom_options = $data['custom_options'];
            $setting->custom_submit = $data['custom_submit'];
            $setting->custom_cancel = $data['custom_cancel'];
            $setting->required_reason = $data['required_reason'];

            $setting->save();

            $user = Auth::user();

            if (@$data['deleteReasons'] && @$data['deleteReasons']) {
                foreach($data['deleteReasons'] as $del){
                    SsCancellationReason::where('id',$del)->delete();
                }
            }
            if(@$data['reasons'] && @$data['reasons']) {
                foreach($data['reasons'] as $reasons){
                    $cancel_reason = SsCancellationReason::where('id',$reasons['id'])->first();
                    $cancel_reason->is_enabled = $reasons['is_enabled'];
                    $cancel_reason->save();
                }
            }

            if (@$data['widget_active_bg'] && @$data['widget_active_bg_mi']) {
                $payload = [
                    "metafield" => [
                        "id" => $data['widget_active_bg_mi'],
                        "value" => $data['widget_active_bg'],
                        "type" => "string",
                    ],
                ];
                $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields/' . $data['widget_active_bg_mi'] . '.json';
                $response = $user->api()->rest('PUT', $endPoint, $payload);
                if (!$response['errors']) {
                }
            }
            if (@$data['widget_active_text'] && @$data['widget_active_text_mi']) {
                $payload = [
                    "metafield" => [
                        "id" => $data['widget_active_text_mi'],
                        "value" => $data['widget_active_text'],
                        "type" => "string",
                    ],
                ];
                $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields/' . $data['widget_active_text_mi'] . '.json';
                $response = $user->api()->rest('PUT', $endPoint, $payload);
                if (!$response['errors']) {
                }
            }
            if (@$data['widget_heading_text'] && @$data['widget_active_text']) {
                $payload = [
                    "metafield" => [
                        "id" => $data['widget_heading_text_mi'],
                        "value" => $data['widget_heading_text'],
                        "type" => "string",
                    ],
                ];
                $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields/' . $data['widget_heading_text_mi'] . '.json';
                $response = $user->api()->rest('PUT', $endPoint, $payload);
                if (!$response['errors']) {
                }
            }
            if (@$data['widget_default_selection'] && @$data['widget_default_selection_mi']) {
                $payload = [
                    "metafield" => [
                        "id" => $data['widget_default_selection_mi'],
                        "value" => $data['widget_default_selection'],
                        "type" => "string",
                    ],
                ];
                $endPoint = 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields/' . $data['widget_default_selection_mi'] . '.json';
                $response = $user->api()->rest('PUT', $endPoint, $payload);
                if (!$response['errors']) {
                }
            }
            if (@$data['restricted_content']) {
                $this->saveMetafields($user, 'restricted', 'string', '', '', $data['restricted_content']);
            }

            DB::commit();
            return response()->json(['data' => 'Saved!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            logger("============= ERROR ::  store =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * Send test mail
     * @param  PlanRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMail(PlanRequest $request)
    {
        try {
            $shopID = get_shopID_H();
            $setting = SsSetting::where('shop_id', $shopID)->first();
            $data = $request->data;

            $res = sendMailH($data['subject'], $data['html_body'], $setting->email_from_email, $data['mailto'], $setting->email_from_name, $shopID, '');

            $msg = ($res == 'success') ? 'Test email sent successfully' : $res;
            return response()->json(['data' => $msg], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  sendMail =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param  PlanRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function emailBody(PlanRequest $request)
    {
        try {
            $shopID = get_shopID_H();
            $data = $request->data;
            $email = SsEmail::where('shop_id', $shopID)->where('category', $data['category'])->first();
            $email->subject = $data['subject'];
            $email->html_body = $data['html_body'];
            $email->days_ahead = $data['days_ahead'];
            $email->save();
            return response()->json(['data' => 'Saved!'], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  emailBody =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request)
    {
        try {
            $image = ImageTrait::makeImage($request->image, 'uploads/mail');
            $url = Storage::disk('public')->url('uploads/mail/') . $image;
            return response()->json(['data' => $url], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  uploadImage =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function installTheme(Request $request)
    {
        try {
            $user = Auth::user();
            installThemeH($request['data']['id'], $user->id);
            return response()->json(['data' => 'Theme installed successfully'], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  installTheme =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function addNewReason(Request $request){
        $shopID = get_shopID_H();
        $metafields = getShopMetaFields();
        $new_reason = new SsCancellationReason;
        $new_reason->shop_id = $shopID;
        $new_reason->reason = $request['data'];
        $new_reason->is_enabled = 0;
        $new_reason->save();
        $setting = SsSetting::where('shop_id', $shopID)->first();
        foreach ($metafields as $key => $field) {
            $settingKey = $field['key'];
            $metaIDKey = $field['key'] . '_mi';
            $setting[$settingKey] = $field['value'];
            $setting[$metaIDKey] = $field['id'];
        }
        $getReason =  SsCancellationReason::where('shop_id',$shopID)->get();
        $setting->reasons = $getReason ? $getReason : '';
        $data['setting'] = $setting;
        $response['data'] = $data;
        return response()->json(['data' => $response], 200);
    }
}
