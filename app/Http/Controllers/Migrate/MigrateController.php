<?php

namespace App\Http\Controllers\Migrate;

use App\Http\Controllers\Controller;
use App\Traits\MigrateTrait;
use App\Traits\ImageTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Jobs\MigrateMembershipsJob;
use App\Jobs\MigrateOneTimeMembershipJob;
use App\Jobs\MigrateMembershipFromAppJob;
use App\Http\Requests\MigrateMemberRequest;
use Maatwebsite\Excel\Facades\Excel;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class MigrateController extends Controller
{
    use MigrateTrait;
    public function index(Request $request)
    {
        try {
            $domain = $request->domain;
            // $user = Auth::user();
            $user = User::where('name', $domain)->first();
            if (!$user) {
                $data['isSuccess'] = false;
                $data['message'] = 'User not found';
                return response()->json(['data' => $data], 200);
            }
            // $res = $this->isExistCustomer($user, 'ruchita.crawlapps@gmail.com');
            // dd($res);

            // START :: Running Code

            $res = $this->checkIsMigrationCSV($request->file('file'), 'recurring');

            if (!$res['isSuccess']) {
                return response()->json(['data' => $res], 200);
            }

            // $isEnableLegacy = $this->checkShopFeature($user, 'legacySubscriptionGatewayEnabled');

            $data['isSuccess'] = false;
            // if($isEnableLegacy === false){
            //     $data['message'] = 'You have not enabled your payment gateway';
            // }elseif ($isEnableLegacy !== true){
            //     $data['message'] = $isEnableLegacy;
            // }else{
            $isEligibleForMigration = $this->checkShopFeature($user, 'eligibleForSubscriptionMigration');

            if ($isEligibleForMigration === false) {
                $data['message'] = 'You are not eligible for subscription migration';
            } elseif ($isEligibleForMigration !== true) {
                $data['message'] = $isEligibleForMigration;
            } else {
                // run csv import script
                $data['isSuccess'] = true;
                // check for correct CSV

                $file = ImageTrait::makeImage($request->file('file'), 'uploads/csv_file/');
                $file_path = Storage::disk('public')->path('uploads/csv_file/' . $file);
                $res = MigrateMembershipsJob::dispatch($file_path, $user->id)->onQueue('UpdateServer');
                $data['message'] = 'Membership migrating in background';
            }
            // }
            return response()->json(['data' => $data], 200);

            // END :: Running Code

            // $row = [
            //         "gateway_customer_id" => "cus_JIyPn61qNonhSb",
            //            "customer_shopify_id" => null,
            //            "customer_email" => "admin+import1@simplee.best",
            //            "customer_firstname" => "Imported",
            //            "customer_lastname" => "Customer1",
            //            "subscription_number" => 1,
            //            "next_billing_date" => "2022-05-01",
            //            "currency_code" => "CAD",
            //            "subscription_status" => "active",
            //            "billing_interval_type" => "month",
            //            "billing_interval_count" => 1,
            //            "delivery_interval_type" => "month",
            //            "delivery_interval_count" => 1,
            //            "is_prepaid" => 0,
            //            "prepaid_orders_completed" => null,
            //            "prepaid_renew" => null,
            //            "min_cycles" => null,
            //            "max_cycles" => null,
            //            "order_count" => null,
            //            "shipping_firstname" => "Frank",
            //            "shipping_lastname" => "Sinatra",
            //            "shipping_address1" => "99 Wall St",
            //            "shipping_address2" => "Suite 111",
            //            "shipping_city" => "New York",
            //            "shipping_state" => "NY",
            //            "shipping_countrycode" => "US",
            //            "shipping_zip" => 10005,
            //            "shipping_price" => 9.99,
            //            "line_item_qty" => 2,
            //            "line_item_price" => 1.99,
            //            "line_item_product_id" => 5981386735782,
            //            "line_item_variant_id" => 37730809839782,
            //            "customer_id" => 4385689960614

            // ];

            //           steps to migrate subscription contract
            //           https://shopify.dev/apps/subscriptions/migrate/contracts


            // check for correct CSV
            $res = $this->checkIsMigrationCSV($request->file('file'));

            if (!$res['isSuccess']) {
                return response()->json(['data' => $res], 200);
            }
            $file = ImageTrait::makeImage($request->file('file'), 'uploads/csv_file/');
            $file_path = Storage::disk('public')->path('uploads/csv_file/' . $file);

            // $file = 'TEMPLATE - Simplee Import - Sheet1.csv';
            // $file_path = Storage::disk('public')->path('uploads/csv_file/' . $file);


            // $this->checkIsMigrationCSV($request->file('file'));

            $res = MigrateMembershipsJob::dispatch($file_path, $user->id)->onQueue('UpdateServer');
            $data['message'] = 'Membership migrating in background';
            return response()->json(['data' => $data], 200);



            $paymentMethodIdResult = $this->createCustomerPaymentMethod($row, $user->id);

            // logger(json_encode($paymentMethodIdResult));
            if (!$paymentMethodIdResult['success']) {
                return response()->json(['data' => $paymentMethodIdResult], 200);
            }

            $row['paymentMethodId'] = $paymentMethodIdResult['message'];
            // $row['paymentMethodId'] = 'gid://shopify/CustomerPaymentMethod/d890f0d7275aa527c84af88884ed44e';

            $ContractDraftIdResult = $this->createSubscriptionContractInShopify($row, $user->id);

            if (!$ContractDraftIdResult['success'] && $ContractDraftIdResult['message'] == '') {
                return response()->json(['data' => $ContractDraftIdResult], 200);
            }

            $lineItem['price'] = $row['line_item_price'];
            $lineItem['discount_type'] = '%';
            $lineItem['discount_amount'] = 0;
            $lineItem['final_amount'] = $row['line_item_price'];
            $lineItem['shopify_variant_id'] = $row['line_item_variant_id'];
            $lineItem['quantity'] = $row['line_item_qty'];

            $contractLineAddResult = $this->subscriptionDraftLineAdd($user->id, $lineItem, $ContractDraftIdResult['message']);
            // $res = $this->commitDraft($user->id, $ContractDraftIdResult['message']);

            //            $this->migrateSubscriptions($user);
        } catch (\Exception $e) {
            logger("============= ERROR ::  index =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function onetimeMigrate(Request $request)
    {
        try {
            $domain = $request->domain;
            // $user = Auth::user();
            $user = User::where('name', $domain)->first();
            if (!$user) {
                $data['isSuccess'] = false;
                $data['message'] = 'User not found';
                return response()->json(['data' => $data], 200);
            }

            // $res = $this->checkIsMigrationCSV($request->file('file'), 'onetime');
            // if(!$res['isSuccess']) {
            //     return response()->json(['data' => $res], 200);
            // }
            $file = ImageTrait::makeImage($request->file('file'), 'uploads/csv_file/');
            $file_path = Storage::disk('public')->path('uploads/csv_file/' . $file);

            $res = MigrateOneTimeMembershipJob::dispatch($file_path, $user->id)->onQueue('UpdateServer');
            $data['message'] = 'Membership migrating in background';
            $data['isSuccess'] = true;
            return response()->json(['data' => $data], 200);
        } catch (\Exception $e) {
            logger("=========== ERROR :: onetimeMigrate =============");
            logger(json_encode($e));
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function merchantMembershipMigration(MigrateMemberRequest $request)
    {
        try {
            $d['isSuccess'] = true;
            $user = Auth::user();

            if ($request->importType == 0) {
                $sessionKey = 'MissingCustomers' . $user->id;
                session([$sessionKey => []]);

                // Import single member
                $data = $request->data;
                $this->migrateMember($data, $user, $sessionKey);

                // $is_expired = $this->is_membership_expired($user);
                // $this->membershipexpireMetaUpdate($user, $is_expired);

                $sessionData = Session::get($sessionKey);
                if (!empty($sessionData)) {
                    $d['data'] = json_encode($sessionData);
                    $d['isSuccess'] = false;
                } else {
                    $shopID = get_shopID_H();

                     $is_membership_expired = $this->is_membership_expired($user);


                     if($is_membership_expired == true)
                     {
                            $this->membershipexpireMetaUpdate($user,true);
                     }

                    Cache::forget($shopID);
                    $d['data'] = 'Membership created successfully';
                    $d['isSuccess'] = true;
                    $d['shop'] = $this->getShopdata();
                }
            } else {
                // Import CSV

                $is_expired = $this->is_membership_expired($user);
                if($is_expired){
                    $d['data'] = 'Free Membership Expired';
                    $d['isSuccess'] = true;
                    return response()->json(['data' => $d], ($d['isSuccess']) ? 200 : 422);
                }
                $data = json_decode($request->form, true);
                $res = $this->checkIsAppMigrationCSV($request->file);

                if (!$res['isSuccess']) {
                    return response()->json(['data' => $res], 200);
                } else {
                    $file = ImageTrait::makeImage($request->file, 'public/uploads/csv_file/');
                    $file_path = Storage::disk('public')->path('/uploads/csv_file/' . $file);

                    MigrateMembershipFromAppJob::dispatch($file_path, $user->id, $data);
                }
                $shopID = get_shopID_H();
                Cache::forget($shopID);

                $d['data'] = 'You will be notified by email once all memberships are created';
                $d['isSuccess'] = true;
                $d['shop'] = $this->getShopdata();

            }



            return response()->json(['data' => $d], ($d['isSuccess']) ? 200 : 422);
        } catch (\Exception $e) {
            logger("============= ERROR ::  merchantMembershipMigration =============");
            logger($e);
            Bugsnag::notifyException($e);
            return response()->json(['data' => $e], 422);
        }
    }
}
