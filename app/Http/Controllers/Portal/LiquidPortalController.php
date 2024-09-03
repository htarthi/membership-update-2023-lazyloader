<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shop;
use App\Models\SsPortal;
use App\Traits\ShopifyTrait;
use Liquid\Template;

class LiquidPortalController extends Controller
{
    use ShopifyTrait;

    public function getThemeFiles()
    {
        try {
            $user_id = Auth::user()->id;
            $shop = Shop::where('user_id', $user_id)->first();

            $db_portals = SsPortal::select('portal_liquid AS liquid', 'portal_css AS css', 'portal_js AS js')->where('shop_id', $shop->id)->first();
            if (!$db_portals) {
                // Add portals code in db
                $this->createPortalsInDB($shop->id);
                $db_portals = SsPortal::select('portal_liquid AS liquid', 'portal_css AS css', 'portal_js AS js')->where('shop_id', $shop->id)->first();
            }

            $data['portal'] = $db_portals;
            return response()->json(['data' => $data], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  getThemeFiles =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function storeThemeFiles(Request $request)
    {
        try {
            $data = $request['data'];
            $template = new Template();
            $template->parse($data['liquid']);

            $user_id = Auth::user()->id;
            $shop = Shop::where('user_id', $user_id)->first();
            $db_portals = SsPortal::where('shop_id', $shop->id)->first();
            $db_portals->portal_liquid = $data['liquid'];
            $db_portals->portal_css = $data['css'];
            $db_portals->portal_js = $data['js'];
            $db_portals->save();

            $res['portal']['liquid'] = $db_portals['portal_liquid'];
            $res['portal']['css'] = $db_portals['portal_css'];
            $res['portal']['js'] = $db_portals['portal_js'];
            return response()->json(['data' => $res, 'isSuccess' => true, 'message' => 'Saved'], 200);
        } catch (\Liquid\Exception\ParseException $e) {
            logger("============= ERROR ::  storeThemeFiles =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            logger("============= ERROR ::  storeThemeFiles =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }
}
