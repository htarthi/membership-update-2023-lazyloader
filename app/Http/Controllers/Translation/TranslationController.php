<?php

namespace App\Http\Controllers\Translation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
	public function index(Request $request)
	{
		try {
			$user = Auth::user();
			$shop = Shop::where('user_id', $user->id)->first();
			$languages = SsLanguage::where('shop_id', $shop->id)->first();

			$data['languages'] = $languages;
			return response()->json(['data' => $data['languages']], 200);
		} catch (\Exception $e) {
			logger("============= ERROR ::  index =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
		}
	}
}
