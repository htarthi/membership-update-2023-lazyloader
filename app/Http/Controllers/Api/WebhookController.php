<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\WebhookTrait;
use Illuminate\Http\Request;
use App\Events\HandleWebhooks;

class WebhookController extends Controller
{
    use WebhookTrait;
    public function index(Request $request)
    {
        try {
            logger("=========== START:: webhook index ===========");
            event(new HandleWebhooks($request));
            // $this->webhookIndex($request);

            return response()->json(['data' => 'success'], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  index =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
        }
    }
}
