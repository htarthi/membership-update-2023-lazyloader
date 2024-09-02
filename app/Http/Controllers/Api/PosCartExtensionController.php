<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\SsCustomer;
use App\Models\SsContract;
use App\Models\SsPosDiscounts;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PosCartExtensionController extends Controller
{
    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function promotions(Request $request)
    {
        try {
            logger('============ promotions API ===========');
            $data = $request->json()->all();

            // logger(json_encode($data));
            $returnArr = $this->getActions($data['customer_id'], $data['shopify_domain']);
            // $returnArr = $this->getActions(4385689960614, 'simplee-test-2.myshopify.com');

            // logger(json_encode($returnArr));
            return response()->json($returnArr, 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  promotions =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function performAction(Request $request)
    {
        try {
            logger('============ performAction API ===========');
            $returnArr = [
                "type" => "simple_action_list",
                "points_label" => "Member discount applied",
                "actions" => [],
            ];
            // logger(json_encode($returnArr));
            return response()->json($returnArr, 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  performAction =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function revertAction(Request $request)
    {
        try {
            logger('============ revertAction API ===========');
            $data = $request->json()->all();
            $returnArr = $this->getActions($data['customer_id'], $data['shopify_domain']);

            // logger(json_encode($returnArr));
            return response()->json($returnArr, 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  revertAction =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function getActions($sh_customerID, $domain)
    {
        try {
            $returnArr = [
                "type" => "simple_action_list",
                "points_label" => "Not a member",
                "actions" => [],
            ];
            //
            //            $returnArr = [
            //                "type" => "simple_action_list",
            //                "points_label" => "Member Discount",
            //                "actions" => [
            //                    [
            //                        "type"=> "percent_discount",
            //                        "title"=> "Apply 20% member discount",
            //                        "description"=> "Member discount of 20%",
            //                        "action_id"=> "Membership 20%",
            //                        "value"=> "0.2"
            //                    ],
            //                    [
            //                        "type"=> "flat_discount",
            //                        "title"=> "Apply 50 member discount",
            //                        "description"=> "Member discount of 50",
            //                        "action_id"=> "Membership 50",
            //                        "value"=> "50"
            //                    ]
            //                ],
            //            ];
            ////
            //            return $returnArr;
            if ($sh_customerID > 0) {

                $user = User::where('name', $domain)->where('password', '!=', null)->where('plan_id', '!=', null)->where('active', 1)->first();

                // logger($user);
                if ($user) {
                    $shop = Shop::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
                    $customer = SsCustomer::where('shop_id', $shop->id)->where('shopify_customer_id', $sh_customerID)->first();

                    if ($customer) {
                        $returnArr['points_label'] = 'Member Discount';

                        $query = SsContract::query();

                        $query = $query->select('ss_contracts.id AS contract_id', 'ss_contracts.shop_id AS shop_id', 'ss_contracts.status AS contract_status', 'ss_contracts.next_processing_date', 'ss_plans.name AS membership_name', 'ss_contracts.ss_plan_id AS ss_plan_id', 'ss_contracts.ss_plan_groups_id AS ss_plan_groups_id')
                            ->join('ss_plans', 'ss_contracts.ss_plan_id', '=', 'ss_plans.id')
                            ->where('ss_contracts.shopify_customer_id', $sh_customerID)
                            ->where('ss_contracts.user_id', $user->id);

                        $query = $query->where('ss_contracts.status', 'active')->where('ss_contracts.shopify_customer_id', $sh_customerID)
                            ->where('ss_contracts.user_id', $user->id);
                        $query = $query->orWhere('ss_contracts.status', 'cancelled')->where('ss_contracts.next_processing_date', '>', Carbon::now())->where('ss_contracts.shopify_customer_id', $sh_customerID)
                            ->where('ss_contracts.user_id', $user->id);

                        $contract = $query->orderBy('ss_contracts.created_at', 'desc')->first();

                        if ($contract) {
                            $discounts = SsPosDiscounts::where('ss_plan_groups_id', $contract->ss_plan_groups_id)->where('shop_id', $contract->shop_id)->get();

                            $actions = [];
                            if (count($discounts) > 0) {
                                $actions = $discounts->map(function ($discount) {
                                    $type = ($discount->discount_amount_type == '%') ? 'percent_discount' : 'flat_discount';
                                    $amount = ($discount->discount_amount_type == '%') ? number_format(($discount->discount_amount / 100), 2) : number_format($discount->discount_amount, 2);

                                    $value = ($discount->discount_amount_type == '%') ? ($amount * 100) . '%' : $discount->discount_amount_type . $amount;

                                    return [
                                        "type" => "$type",
                                        "title" =>  "Apply $value member discount",
                                        "description" =>  "Member discount of $value",
                                        "action_id" =>  "Membership $value",
                                        "value" =>  $amount
                                    ];
                                });
                            }

                            $returnArr = [
                                "type" => "simple_action_list",
                                "points_label" => "Active Member ($contract->membership_name)",
                                "actions" => $actions
                            ];
                        }
                    }
                }
            }

            return $returnArr;
        } catch (\Exception $e) {
            logger("============= ERROR ::  getActions =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }
}
