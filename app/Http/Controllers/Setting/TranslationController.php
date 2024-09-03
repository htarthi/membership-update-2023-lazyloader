<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\TranslationRequest;
use App\Models\Shop;
use App\Models\SsCustomer;
use App\Models\SsLanguage;
use App\Traits\ShopifyTrait;
use App\Models\User;
class TranslationController extends Controller
{
    use ShopifyTrait;
    public function index(Request $request)
    {
        // dd($request);
        try {
            $user = Auth::user();
            $shop = Shop::where('user_id', $user->id)->first();

            $data['languages'] = $this->createLanguageInDb($shop->id);
            $response = $user->api()->rest('GET', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields.json');
        
			if (!$response['errors'] && $response['body']['metafields']) {
				foreach ($response['body']['metafields'] as $metafield) {
					if ($metafield['key'] == "mem_biling_info") {
						$data['languages']['mem_biling_info'] = $metafield['value'];
					}
                    if ($metafield['key'] == "mem_resume") {
						$data['languages']['mem_resume'] = $metafield['value'];
					}
                    if ($metafield['key'] == "mem_upcoming") {
						$data['languages']['mem_upcoming'] = $metafield['value'];
					}
				}
			}
            return response()->json(['data' => $data], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  index =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function store(TranslationRequest $request)
    {
        try {
            $user = Auth::user();
            $shop = Shop::where('user_id', $user->id)->first();
            $updateArray = $request->data;
            if(isset($request->data['mem_biling_info']) && isset($request->data['mem_resume']) && isset($request->data['mem_upcoming'])) {
                $fields = [
                    'mem_biling_info' => $request->data['mem_biling_info'],
                    'mem_resume' => $request->data['mem_resume'],
                    'mem_upcoming' => $request->data['mem_upcoming']
                ];
                foreach ($fields as $key => $label) {
                    if (isset($request->data[$key])) {
                        $parameter = [
                            "metafield" => [
                                'namespace' => 'simplee',
                                'key' => $key,
                                'value' => $request->data[$key],
                                'type' => 'string'
                            ]
                        ];
                        $user->api()->rest('POST', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields.json', $parameter);
                        unset($updateArray[$key]);
                    }
                }
            }
            $languages = SsLanguage::where('shop_id', $shop->id)->update($updateArray);
            return response()->json(['data' => 'Translations saved successfully'], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  store =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    // public function getmetafields($name){
    //     $user = User::where('name',$name)->first();
    //     $result = $user->api()->rest('GET', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields.json');
    //     return $result;
    //     // return response()->json(['data' =>  $result], 200);
    //     // return $result['body']['metafields'];
    // }

    public function getmetafields($name){
        $user = User::where('name', $name)->first();
        if ($user) {
            $result = $user->api()->rest('GET', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields.json');
            return response()->json(['data' => $result['body']['metafields']], 200);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    public function getCustomerShop($id){
        $customer = SsCustomer::where('shopify_customer_id',$id)->first();
        if ($customer) {
            $shop = Shop::where('id',$customer->shop_id)->first();
            $user = User::where('id',$shop->user_id)->first();
            return response()->json(['data' => $user->name], 200);
        } else {
            return response()->json(['error' => 'Customer not found'], 404);
        }
    }


}
