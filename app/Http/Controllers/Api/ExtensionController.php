<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\SsPlan;
use App\Models\SsPlanGroup;
use App\Models\SsPlanGroupVariant;
use App\Traits\ShopifyTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExtensionController extends Controller
{
    use ShopifyTrait;

    public function getPlan(Request $request)
    {
        try {
            $headerT = $request->header('X-session-token');
            $payload = json_decode(base64_decode(substr($headerT, (strpos($headerT, ".") + 1), (strrpos($headerT, ".") - strpos($headerT, ".") - 1))));

            $domain = str_replace('https://', '', $payload->dest);
            // $domain = 'simplee-test-2.myshopify.com';
            $user = User::where('name', $domain)->first();
            $shop = Shop::where('user_id', $user->id)->first();
            $data = $request->json()->all();
            $sellingPlanGroupId = str_replace('gid://shopify/SellingPlanGroup/',  '', $data['sellingPlanGroupId']);
            // $sellingPlanGroupId = '32440486';
            // $sellingPlanGroupId = '31883430';
            $dbPlanGroup = SsPlanGroup::where('shop_id', $shop->id)->where('shopify_plan_group_id', $sellingPlanGroupId)->first();
            $dbPlan = SsPlan::where('shop_id', $shop->id)->where('ss_plan_group_id', $dbPlanGroup->id)->get();

            if (count($dbPlan) > 1) {
                $res['success'] = false;
            } else {
                $dbPlan = $dbPlan->first();
                $res['success'] = true;
                $res['id'] = $dbPlanGroup->id;
                $res['planTitle'] = $dbPlanGroup->name;
                $res['customer_tag'] = $dbPlanGroup->tag_customer;
                $res['billingFrequency'] = $dbPlan->billing_interval_count;
                $res['billingFrequencyInterval'] = $dbPlan->billing_interval;
                $res['billingFrequencyIntervalLabel'] = ucwords($dbPlan->billing_interval) . '(s)';
                $res['percentageOff'] = $dbPlan->pricing_adjustment_value;
                $res['is_prepaid'] = $dbPlan->is_prepaid;
            }

            return response()->json(['data' => $res], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  getPlan =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function planGroups(Request $request)
    {
        try {
            $headerT = $request->header('X-session-token');
            $payload = json_decode(base64_decode(substr($headerT, (strpos($headerT, ".") + 1), (strrpos($headerT, ".") - strpos($headerT, ".") - 1))));

            $domain = str_replace('https://', '', $payload->dest);
            // $domain = 'simplee-test-2.myshopify.com';
            $user = User::where('name', $domain)->first();
            $shop = Shop::where('user_id', $user->id)->first();


            $dbPlanGroup = SsPlanGroup::select('id', 'shopify_plan_group_id', 'name')->where('shop_id', $shop->id)->where('user_id', $user->id)->get()->toArray();

            return response()->json(['data' => $dbPlanGroup], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  planGroups =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function createPlan(Request $request)
    {
        try {
            $headerT = $request->header('X-session-token');
            $payload = json_decode(base64_decode(substr($headerT, (strpos($headerT, ".") + 1), (strrpos($headerT, ".") - strpos($headerT, ".") - 1))));

            $domain = str_replace('https://', '', $payload->dest);
            $data = $request->json()->all();
            $user = User::where('name', $domain)->first();
            $shop = Shop::where('user_id', $user->id)->first();

            $planData = $data['planData'];

            try {
                DB::beginTransaction();

                $shopify_group_id = str_replace('gid://shopify/SellingPlanGroup/', '', $data['id']);
                // create plan group
                $planGCount = SsPlanGroup::where('shop_id', $shop->id)->count();
                ($planGCount > 0) ? '' : $this->event($shop->user_id, 'Onboarding', 'First Group Created', 'Merchant created group [' . $planData['planTitle'] . ']');

                $is_existplan = SsPlanGroup::where('shop_id', $shop->id)->where('shopify_plan_group_id', $shopify_group_id)->first();
                $plangroup = ($is_existplan) ? $is_existplan : new SsPlanGroup;
                $plangroup->shop_id = $shop->id;
                $plangroup->user_id =  $shop->user_id;
                $plangroup->active = 1;
                $plangroup->name = $planData['planTitle'];
                // $plangroup->merchantCode = ($is_existplan) ? $is_existplan->merchantCode : strtolower(str_replace(' ', '-', $planData['planTitle']));
                $plangroup->merchantCode = ($is_existplan) ? $is_existplan->merchantCode : strtolower(str_replace(' ', '-', $planData['planTitle']));
                $plangroup->description = ($is_existplan) ? $is_existplan->description : $planData['planTitle'];
                $plangroup->position = ($is_existplan) ? $is_existplan->position : SsPlanGroup::where(
                    'shop_id',
                    $shop->id
                )->where('active', 1)->count();
                $plangroup->options = 'Membership Length';
                $plangroup->tag_customer = $planData['customer_tag'];
                $plangroup->save();

                //                create plan

                $planCount = SsPlan::where('shop_id', $shop->id)->count();
                ($planCount > 0) ? '' : $this->event($shop->user_id, 'Onboarding', 'First Plan Created', 'Merchant created plan [' . $planData['planTitle'] . ']');

                $is_existplan = SsPlan::where('shop_id', $shop->id)->where('ss_plan_group_id', $plangroup->id)->orderBy('created_at', 'asc')->first();
                $plan = ($is_existplan) ? $is_existplan : new SsPlan;
                $plan->shop_id = $shop->id;
                $plan->user_id = $shop->user_id;
                $plan->ss_plan_group_id = $plangroup->id;
                $plan->name = $planData['billingFrequency'] . ' ' . $planData['billingFrequencyInterval'];
                $plan->description = $planData['billingFrequency'] . ' ' . $planData['billingFrequencyInterval'];
                $plan->options = $planData['billingFrequency'] . ' ' . $planData['billingFrequencyInterval'];
                $plan->status = 'active';
                $plan->is_prepaid = 0;
                $plan->position = ($is_existplan) ? $is_existplan->position : SsPlan::where('shop_id', $shop->id)->where('ss_plan_group_id', $plangroup->id)->count();

                $plan->billing_interval = $planData['billingFrequencyInterval'];
                $plan->billing_interval_count = $planData['billingFrequency'];

                $plan->delivery_interval = $planData['billingFrequencyInterval'];
                $plan->delivery_interval_count = $planData['billingFrequency'];

                $plan->pricing_adjustment_type = 'PRICE';
                $plan->pricing_adjustment_value = $planData['percentageOff'];

                $plan->billing_min_cycles = null;
                $plan->billing_max_cycles = null;

                $plan->billing_anchor_day = null;
                $plan->billing_anchor_type = null;
                $plan->billing_anchor_month = null;

                $plan->delivery_anchor_day = null;
                $plan->delivery_anchor_type = null;
                $plan->delivery_anchor_month = null;

                $plan->delivery_cutoff = null;
                $plan->delivery_pre_cutoff_behaviour = null;
                $plan->save();

                // create/edit selling plan in shopify
                $result = $this->ShopifySellingPlan($shop->user_id, $plan);

                if ($result == 'success') {
                    // assign product

                    if ($data['id'] == '') {
                        $result = $this->assignProduct($shop, str_replace('gid://shopify/Product/',  '', $data['productId']), $plangroup->id);

                        if ($result == 'success') {
                            $msg = 'Saved!';
                            $success = true;
                        } else if (!$result) {
                            $msg = 'Error - please try again';
                            $success = false;
                        } else {
                            $msg = $result;
                            $success = false;
                        }
                    } else {
                        $msg = 'Saved!';
                        $success = true;
                    }

                    if ($success) {
                        $result = $this->createSegment($user, $plangroup->name, $plangroup->tag_customer, $plangroup->shopify_css_id);
                        if ($result['msg'] == 'success') {
                            $plangroup->shopify_css_id = $result['id'];
                            $plangroup->save();
                            DB::commit();
                        } else {
                            DB::rollBack();
                        }
                    }
                } else if (!$result) {
                    DB::rollBack();
                    $msg = 'Error - please try again';
                    $success = false;
                } else {
                    DB::rollBack();
                    $msg = $result;
                    $success = false;
                }

                return response()->json(['data' => $msg, 'isSuccess' => $success], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['data' => $e, 'isSuccess' => false], 422);
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  createPlan =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function assignProduct($shop, $productId, $planGroupId)
    {
        try {
            DB::beginTransaction();


            $plangroup = SsPlanGroup::find($planGroupId);
            $user = User::find($shop->user_id);
            $is_exist = SsPlanGroupVariant::where('ss_plan_group_id', $planGroupId)->where('shopify_product_id', $productId)->first();
            $sh_product = $this->getShopifyData($user, $productId, 'product', 'id,title');

            logger("========= assignProduct ==========");
            // logger(json_encode($sh_product));

            $variants = ($is_exist) ? $is_exist : new SsPlanGroupVariant;

            $variants->shop_id =  $shop->id;
            $variants->user_id =  $shop->user_id;
            $variants->ss_plan_group_id = $planGroupId;
            $variants->shopify_product_id = $productId;
            $variants->product_title = $sh_product['title'];
            $variants->last_sync_date = date('Y-m-d H:i:s');
            $variants->save();

            if (!$is_exist) {
                $result = $this->updateSellingPlanGroupProduct($shop->user_id, (array)$productId,  $plangroup->shopify_plan_group_id);
                if ($result == 'success') {
                    DB::commit();
                }
                return $result;
            } else {
                return 'success';
            }
        } catch (\Exception $e) {
            DB::rollBack();
            logger("============= ERROR ::  assignProduct =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function removeProduct(Request $request)
    {
        try {
            DB::beginTransaction();
            $headerT = $request->header('X-session-token');
            $payload = json_decode(base64_decode(substr($headerT, (strpos($headerT, ".") + 1), (strrpos($headerT, ".") - strpos($headerT, ".") - 1))));

            $domain = str_replace('https://', '', $payload->dest);
            // $domain = 'simplee-test-2.myshopify.com';
            $data = $request->json()->all();

            $productId = str_replace('gid://shopify/Product/',  '', $data['productId']);
            $sellingPlanGroupId = str_replace('gid://shopify/SellingPlanGroup/',  '', $data['sellingPlanGroupId']);

            $user = User::where('name', $domain)->first();
            $shop = Shop::where('user_id', $user->id)->first();

            $result = $this->updateSellingPlanGroupRemoveProduct($shop->user_id, (array)$productId,  $sellingPlanGroupId);
            if ($result == 'success') {
                $dbPlanG = SsPlanGroup::where('shopify_plan_group_id', $sellingPlanGroupId)->first();
                if ($dbPlanG) {
                    $dbProduct = SsPlanGroupVariant::where('ss_plan_group_id', $dbPlanG->id)->where('shopify_product_id', $productId)->first();
                    ($dbProduct) ? $dbProduct->delete() : '';
                    $res = $this->planDestroy($sellingPlanGroupId, $shop->id);
                }

                $result = $this->removeSegment($user, $dbPlanG->shopify_css_id);
                if ($result == 'success') {
                    $dbPlanG->delete();
                    DB::commit();
                }
                DB::commit();
                $msg = 'Saved!';
                $success = true;
            } else if (!$result) {
                DB::rollBack();
                $msg = 'Error - please try again';
                $success = false;
            } else {
                DB::rollBack();
                $msg = $result;
                $success = false;
            }
            return response()->json(['data' => $msg, 'isSuccess' => $success], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            logger("============= ERROR ::  removeProduct =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function add(Request $request)
    {
        try {
            $headerT = $request->header('X-session-token');
            $payload = json_decode(base64_decode(substr($headerT, (strpos($headerT, ".") + 1), (strrpos($headerT, ".") - strpos($headerT, ".") - 1))));

            $domain = str_replace('https://', '', $payload->dest);
            // $domain = 'simplee-test-2.myshopify.com';
            $user = User::where('name', $domain)->first();
            $shop = Shop::where('user_id', $user->id)->first();
            $data = $request->json()->all();
            $productId = str_replace('gid://shopify/Product/',  '', $data['productId']);
            foreach ($data['planData'] as $key => $value) {
                $result = $this->assignProduct($shop, $productId, $value);
            }

            $isSuccess = ($result) == 'success' ? true : false;
            return response()->json(['data' => $result, 'isSuccess' => $isSuccess], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  add =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function planDestroy($shSellingPlanGroupId, $shopId)
    {
        try {
            $shop = Shop::find($shopId);
            $planGroup = SsPlanGroup::where('shopify_plan_group_id', $shSellingPlanGroupId)->first();
            $plan = SsPlan::where('ss_plan_group_id', $planGroup->id)->first();
            $planCount = SsPlan::where('ss_plan_group_id', $plan->ss_plan_group_id)->count();

            $result = $this->deleteSellingPlan($shop->user_id, $shSellingPlanGroupId, $plan->shopify_plan_id, $planCount);
            $newResult = $this->getControllerReturnData($result, 'Saved');

            if ($newResult['success']) {
                $plan->delete();
                if ($planCount == 1) {
                    $planGroup->delete();
                }
            }

            return $newResult;
        } catch (\Exception $e) {
            logger("============= ERROR ::  planDestroy =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }
}
