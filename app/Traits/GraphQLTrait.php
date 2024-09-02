<?php

namespace App\Traits;

use App\Models\User;
use Gnikyt\BasicShopifyAPI\BasicShopifyAPI;
use Gnikyt\BasicShopifyAPI\Options;
use Gnikyt\BasicShopifyAPI\Session;
/**
 * Trait GraphQLTrait.
 */
trait GraphQLTrait
{
    /**
     * The current API call limits from last request.
     *
     * @var array
     */
    protected $apiCallLimits = [
        'rest'  => [
            'left'  => 0,
            'made'  => 0,
            'limit' => 40,
        ],
        'graph' => [
            'left'          => 0,
            'made'          => 0,
            'limit'         => 1000,
            'restoreRate'   => 50,
            'requestedCost' => 0,
            'actualCost'    => 0,
        ],
    ];

    protected $requestTimestamp;
    protected $leakRate = 50; // Leak rate per second
    protected $buffer = .1; // buffer seconds

    protected function graph(User $shop, string $query, array $parameters = [], $version)
    {
        $queryCost = $this->calculateQueryCost($query, $parameters);
        $availableCost = $this->getApiCalls('graph', 'left');
        $availableCostSince = $this->requestTimestamp;
        $extraSecondsSince = round(microtime(true) - $availableCostSince, 3);
        $availableCost += ($extraSecondsSince * $this->leakRate);
        if ($queryCost > $availableCost) {
            $requireExtraCost = $queryCost - $availableCost;
            $awaitSeconds = $requireExtraCost / $this->leakRate;

            $awaitSeconds = ceil($awaitSeconds) + 1;
            sleep($awaitSeconds);
        }
        // $version = (env('SHOPIFY_SAPI_VERSION')) ? env('SHOPIFY_SAPI_VERSION') : '2021-07';
        $options = new Options();
        $options->setVersion($version);
        $api = new BasicShopifyAPI($options);
        $api->setSession(new Session(
            $shop->name,
            $shop->password
        ));
        $data = $api->graph($query, $parameters);
        $this->requestTimestamp = end($data['timestamps']);
        return $data;
    }

    protected function rest(User $shop, string $query, array $parameters = [], $method = 'GET')
    {
        // $queryCost = $this->calculateQueryCost($query, $parameters);
        // $availableCost = $this->getApiCalls('rest', 'left');
        // $availableCostSince = $this->requestTimestamp;
        // $extraSecondsSince = round(microtime(true) - $availableCostSince, 3);
        // $availableCost += ($extraSecondsSince * $this->leakRate);
        // \Log::info("---------------------------");
        // \Log::info($queryCost." > ".$availableCost);
        // if ($queryCost > $availableCost) {
        //     $requireExtraCost = $queryCost - $availableCost;
        //     $awaitSeconds = $requireExtraCost / $this->leakRate;

        //     $awaitSeconds = ceil($awaitSeconds) + 1;
        //     \Log::info("sleep: ". ceil($awaitSeconds));
        //     sleep($awaitSeconds);
        // }
        $version = (env('SHOPIFY_SAPI_VERSION')) ? env('SHOPIFY_SAPI_VERSION') : '2021-07';
        $data = $shop->api()->rest($method, $query, $parameters);
        if (!@$data['errors']) {
            $call_limit = $data['response']->getHeader('HTTP_X_SHOPIFY_SHOP_API_CALL_LIMIT')[0];
            $rate = substr($call_limit, 0, strpos($call_limit, '/'));
            if ($rate >= 35) {
                logger("======= Rest API rate :: $rate =======");
                sleep(10);
            }
        }
        $this->requestTimestamp = end($data['timestamps']);
        return $data;
    }

    protected function calculateQueryCost(string $query, array $variable = [])
    {
        try {
            $initialCost = 2;
            $queryArray = $this->parseGraphQLtoArray($query, $variable);
            $firstLimit = null;
            $isFirst = true;
            $queryCost = $initialCost; // Added Initial Cost
            foreach ($queryArray as $key => $value) {
                if ($firstLimit == null) {
                    preg_match('/first:(.*)\d/', $key, $firstLimit);
                    $firstLimit = (int)str_replace('first:', '', $firstLimit[0]);
                }
                if (is_array($value) && !isset($value['edges']['node'])) {
                    $queryCost += $firstLimit; // Added any operational
                }
                if (is_array($value) && isset($value['edges']['node'])) {
                    $subArray = $value['edges']['node'];
                    $queryCost += $this->calculateNodes($subArray, $key, $isFirst, $firstLimit, $initialCost); // Child Nodes
                }
                if ($isFirst) {
                    $isFirst = false;
                }
            }
            $queryCost += $firstLimit; // Added First Limit
            return $queryCost;
        } catch (\Exception $e) {
            logger("============= ERROR ::  calculateQueryCost =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    protected function parseGraphQLtoArray($query, array $variables = [])
    {
        $query = preg_replace('/^[query](.*?)\)/', '', $query, 1);
        if (count($variables)) {
            foreach ($variables as $key => $variable) {
                $query = preg_replace('/\$' . $key . '/', $variable, $query);
            }
        }
        $query = str_replace('"', '\"', $query);
        $query = str_replace("{", "{\n", $query);
        $query = str_replace("}", "\n}\n", $query);
        $query = array_map("trim", explode("\n", $query));
        foreach ($query as $k => $line) {
            // strip comments
            $line = explode("#", $line);
            $line = $line[0];
            // skip opening or closing tags
            if ($line === "{" || $line === "") {
                continue;
            }
            // declare as object value
            if (strpos($line, "{") !== false) {
                $name = trim(str_replace("{", "", $line));
                $query[$k] = '"' . $name . '": {';
                continue;
            }
            if (strpos($line, "}") !== false) {
                $query[$k] .= ',';
                continue;
            }
            $query[$k] = '"' . $line . '": true,';
        }
        $query = implode("", $query);
        // cut last comma
        $query = substr($query, 0, -1);
        // cut trailing commas
        $query = str_replace(",}", "}", $query);
        // produce php array
        $retval = json_decode($query, true);
        if (is_null($retval)) {
            throw new \Exception(sprintf("Error when parsing GraphQL fields: '%s'", $query));
        }
        return $retval;
    }

    protected function calculateNodes(array $array, $arrayKey, $isFirst, $firstLimit, $initialCost, $queryCost = 0)
    {
        if (!$isFirst) {
            preg_match('/first:(.*)\d/', $arrayKey, $limit);
            $limit = (int)str_replace('first:', '', $limit[0]);
            $queryCost += ($initialCost * $firstLimit + ($firstLimit * $limit));
        }
        foreach ($array as $key => $value) {
            if (is_array($value) && !isset($value['edges']['node'])) {
                $queryCost += $firstLimit;
            }
            if (is_array($value) && isset($value['edges']['node'])) {
                $subArray = $value['edges']['node'];
                return $this->calculateNodes($subArray, $key, false, $firstLimit, $initialCost, $queryCost);
            }
        }
        return $queryCost;
    }

    /**
     * @param  string  $type
     * @param  string|null  $key
     * @return int|int[]|mixed
     */
    public function getApiCalls(string $type = 'rest', string $key = null)
    {
        if ($key) {
            $keys = array_keys($this->apiCallLimits[$type]);
            if (!in_array($key, $keys)) {
                // No key like that in array
                throw new Exception('Invalid API call limit key. Valid keys are: ' . implode(', ', $keys));
            }
            // Return the key value requested
            return $this->apiCallLimits[$type][$key];
        }
        // Return all the values
        return $this->apiCallLimits[$type];
    }
}
