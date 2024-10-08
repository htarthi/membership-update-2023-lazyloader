<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Osiset\ShopifyApp\Http\Middleware\AuthProxy;
use Osiset\ShopifyApp\Objects\Values\NullableShopDomain;
use Osiset\ShopifyApp\Services\ShopSession;
use Osiset\ShopifyApp\Traits\ConfigAccessible;
use function Osiset\ShopifyApp\createHmac;
use function Osiset\ShopifyApp\parseQueryString;

class ProxyAuth
{
    use ConfigAccessible;

    /**
     * Shop session helper.
     *
     * @var ShopSession
     */
    protected $shopSession;

    /**
     * Constructor.
     *
     * @param ShopSession $shopSession Shop session helper.
     *
     * @return self
     */
    public function __construct(ShopSession $shopSession)
    {
        $this->shopSession = $shopSession;
    }

    /**
     * Handle an incoming request to ensure it is valid.
     *
     * @param Request  $request The request object.
     * @param \Closure $next    The next action.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Grab the query parameters we need
        $query = $this->getQueryStringParameters($request);
        $signature = isset($query['signature']) ? $query['signature'] : null;

        $shop = NullableShopDomain::fromNative($query['shop'] ?? null);

        if (isset($query['signature'])) {
            // Remove signature since its not part of the signature calculation
            unset($query['signature']);
        }
        // Build a local signature
        $signatureLocal = createHmac(['data' => $query, 'buildQuery' => true], $this->getConfig('api_secret'));
        if ($signature !== $signatureLocal || $shop->isNull()) {
            // Issue with HMAC or missing shop header
            return Response::make('Invalid proxy signature.', 401);
        }

        // Login the shop
        $this->shopSession->make($shop);

        // All good, process proxy request
        return $next($request);
    }

    /**
     * Parse query strings the same way Shopify does.
     *
     * @param Request  $request The request object.
     *
     * @return array
     */
    protected function getQueryStringParameters(Request $request): array
    {
        return parseQueryString($request->server->get('QUERY_STRING'));
    }
}
