<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SsCustomer;
use App\Models\User;
use App\Models\Shop;
use App\Models\SsPortal;
use App\Traits\ShopifyTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;
use Osiset\ShopifyApp\Objects\Values\NullableShopDomain;
use function Osiset\ShopifyApp\createHmac;
use function Osiset\ShopifyApp\parseQueryString;
use App\Http\Controllers\Subscriber\SubscriberController;

class PortalController extends Controller
{
    use ShopifyTrait;
    public function index(Request $request)
    {
        try {
            $data['shop'] = $request->shop;
            $shop = Shop::where('domain', $data['shop'])->orWhere('myshopify_domain', $data['shop'])->first();
            // return Response()->view('portal.index', compact('data'), 200)->withHeaders([
            //     'Content-Type'=>'application/liquid',
            // ]);
            $portal = SsPortal::where('shop_id', $shop->id)->first();
            if (!$portal) {
                $this->createPortalsInDB($shop->id);
                $portal = SsPortal::where('shop_id', $shop->id)->first();
            }
            $portal_style  = $portal->portal_css;
            $portal_js = $portal->portal_js;

            $portal_js = str_replace("{{ host }}", env('APP_URL'), $portal_js);
            $portal_js = str_replace("{{ shop }}", $data['shop'], $portal_js);

            return Response()->view('portal.portal_test', compact('data', 'portal_style', 'portal_js'), 200)->withHeaders([
                'Content-Type' => 'application/liquid',
            ]);
        } catch (\Exception $e) {
            logger("============= ERROR ::  index =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }
}
