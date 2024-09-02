<?php

namespace App\Http\Controllers\Shipping;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShippingRequest;
use App\Http\Resources\ShippingResource;
use App\Models\SsPlanGroup;
use App\Traits\ShopifyTrait;
use App\Models\SsShippingProfile;
use App\Models\SsShippingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShippingController extends Controller
{
    use ShopifyTrait;
    public function index()
    {
        try {
            $shop = getShopH();
            $shipping = SsShippingProfile::with('ShippingZones')->where('shop_id', $shop->id)->get();
            $res['shipping'] = ($shipping) ? ShippingResource::collection($shipping) : [];
            $res['countries'] = array('Rest of World' => 'Rest of World') + countryH();
            $res['shop']['currency'] = $shop->currency_symbol;
            return response()->json(['data' => $res], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  index =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function edit($id)
    {
        try {
            $res['shipping'] = ($id == 0) ? [] : ShippingResource::collection(SsShippingProfile::with('ShippingZones')->where('id', $id)->get())[0];
            $res['countries'] = array('Rest of World' => 'Rest of World') + countryH();
            return response()->json(['data' => $res], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  edit =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function store(ShippingRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->data;

            $shop = getShopH();

            $planGpIds = SsPlanGroup::where('shop_id', $shop->id)->where('active', 1)->where('shopify_plan_group_id', '!=', null)->pluck('shopify_plan_group_id')->toArray();

            $profile = ($data['id'] == 0) ? new SsShippingProfile : SsShippingProfile::find($data['id']);

            $profile->shop_id = $shop->id;
            $profile->name = $data['name'];
            $profile->active = 1;
            $profile->plan_group_ids = json_encode($planGpIds);
            $profile->shopify_location_group_id = ($profile->shopify_location_group_id) ? $profile->shopify_location_group_id  : json_encode([]);
            $profile->save();

            foreach ($data['shipping_zones'] as $key => $val) {
                $zone = (@$val['id']) ? SsShippingZone::find($val['id']) : new SsShippingZone;
                $zone->ss_shipping_profile_id = $profile->id;
                $zone->shopify_zone_id = ($zone->shopify_zone_id) ? $zone->shopify_zone_id : json_encode([]);
                $zone->active = 1;
                $zone->countries = json_encode($val['zone_country']);
                $zone->zone_name = $val['zone_name'];
                $zone->rate_name = $val['rate_name'];
                $zone->rate_value = $val['rate_value'];
                $zone->save();
            }

            // DB::commit();
            $result = $this->createDeliveryProfile($shop->user_id, $profile->id);
            if ($result == 'success') {
                DB::commit();
                $msg = 'Shipping profile updated';
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
            logger("============= ERROR ::  store =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $shop = getShopH();
            $result = $this->removeDeliveryProfile($shop->user_id, $id);

            if ($result == 'success') {
                $zones = SsShippingZone::where('ss_shipping_profile_id', $id)->get();
                foreach ($zones as $key => $val) {
                    $val->delete();
                }
                SsShippingProfile::find($id)->delete();
                DB::commit();
                $msg = 'Shipping profile updated';
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

            // $msg = 'Deleted!';
            // $success = true;
            return response()->json(['data' => $msg, 'isSuccess' => $success], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            logger("============= ERROR ::  destroy =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }
}
