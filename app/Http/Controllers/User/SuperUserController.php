<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\SsContract;
use App\Models\SsCustomer;
use App\Models\SsOrder;
use App\Models\SsPlanGroup;
use App\Models\SsSetting;
use App\Models\SsMigration;
use Illuminate\Http\Request;
use Osiset\ShopifyApp\Storage\Models\Charge;

use Log;

class SuperUserController extends Controller
{
    public function index(Request $request)
    {
        try {
            return view('user.super-user');
        } catch (\Exception $e) {
            logger("============= ERROR ::  index =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function getShop(Request $request)
    {
        try {
            $search_name = $request->shop;
            $shop = Shop::Where('name', 'LIKE', $search_name)->orWhere('myshopify_domain', 'LIKE', $search_name)->orWhere('id', $search_name)->first();
            if ($shop) {
                $data['shop'] = $shop->toarray();

                $charge = Charge::select('trial_ends_on', 'price')->where('user_id', $shop->user_id)->where('status', 'ACTIVE')->first();
                $plan_gps_count = SsPlanGroup::where('shop_id', $shop->id)->count();
                $customers_count = SsCustomer::where('shop_id', $shop->id)->count();
                $active_contracts = SsContract::where('shop_id', $shop->id)->where('status', 'active')->count();
                $setting = SsSetting::select('transaction_fee')->where('shop_id', $shop->id)->first();
                $total_fees_charged = SsOrder::where('shop_id', $shop->id)->where('tx_fee_status', 'processed')->sum('tx_fee_amount');

                $data['charge']['trial_end_on'] = ($charge) ? date("M d, Y", strtotime($charge->trial_ends_on)) : '';
                $data['charge']['price'] = ($charge) ? $charge->price : '';
                $data['selling_plan_groups'] = ($plan_gps_count) ? $plan_gps_count : 0;
                $data['customers'] = ($customers_count) ? $customers_count : 0;
                $data['active_contracts'] = ($active_contracts) ? $active_contracts : 0;
                $data['transaction_fee'] = ($setting) ? number_format($setting->transaction_fee * 100, 2) : 0;
                $data['total_fees_charged'] = ($total_fees_charged) ? number_format($total_fees_charged, 2) : 0;
                $data['shop']['created_at'] = date("M d, Y", strtotime($shop->created_at));

                return response()->json(['data' => $data], 200);
            } else {
                return response()->json(['data' => 'Shop not found...'], 422);
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  getShop =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }
    public function uploadCsv(Request $request)
    {
        try {
            $data = $request->csv;

            $insert = array();
            $import_id = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
            foreach ($data as $key => $migration) {
                // Bulk Insert
                $arr_migration = (array) $migration;
                array_push($insert, array_merge([
                    'import_id' => $import_id,
                    'shop_id' => 1
                ], $migration));
            }
            SsMigration::insert($insert);
            return response()->json(['data' => "Okay"], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  uploadCsv =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }
}
