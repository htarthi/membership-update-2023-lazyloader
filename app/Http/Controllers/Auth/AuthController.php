<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View as ViewView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Osiset\ShopifyApp\Actions\AuthenticateShop;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;

class AuthController extends Controller
{
    use \Osiset\ShopifyApp\Traits\AuthController;
    public function index(Request $request)
    {
        return View::make(
            'auth.index',
            ['shopDomain' => $request->query('shop')]
        );
    }

    /**
     * Authenticating a shop.
     *
     * @param AuthenticateShop $authenticateShop The action for authorizing and authenticating a shop.
     *
     * @return ViewView|RedirectResponse
     */
    public function authenticate(Request $request, AuthenticateShop $authenticateShop)
    {
        // Get the shop domain
        $shopDomain = new ShopDomain($request->get('shop'));

        // Run the action, returns [result object, result status]
        list($result, $status) = $authenticateShop($request);

        if ($status === null) {
            // Go to login, something is wrong
            return Redirect::route('login');
        } elseif ($status === false) {
            // No code, redirect to auth URL
            return $this->oauthFailure($result->url, $shopDomain);
        } else {
            // Everything's good... determine if we need to redirect back somewhere
            $return_to = Session::get('return_to');
            if ($return_to) {
                Session::forget('return_to');
                return Redirect::to($return_to);
            }

            // No return_to, go to home route
            return Redirect::route('home');
        }
    }
}
