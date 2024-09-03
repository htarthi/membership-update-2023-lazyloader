<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\CustomerApiResource;
use App\Models\User;
use App\Models\SsCustomer;
use App\Models\Shop;
use Validator;

class CustomerController extends Controller
{
    public function validateCustomer(Request $request){
    	try{
    		$domain = $request->domain;
    		$customerEmail = $request->email;

    		$validator = Validator::make($request->all(), [
		        'domain' => 'required',
		        'email' => 'required|email',
		    ]);

    		if ($validator->fails())
			{
			    $messages = $validator->messages();
			    return response()->json(['message' => $messages], 422);
			}
    		$user = User::where('name', $domain)->where('password', '!=', null)->where('active', 1)->where('deleted_at', null)->first();

    		if(!$user){
    			return response()->json(['message' => 'No results'], 422);
    		}

    		$shop = Shop::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();

    		$customer = SsCustomer::where('email', $customerEmail)->where('shop_id', $shop->id)->first();

    		if(!$customer){
    			return response()->json(['message' => 'No results'], 422);
    		}

    		$entity = User::select('ss_contracts.id', 'ss_contracts.status AS contract_status', 'ss_contracts.next_processing_date', 'ss_plans.name AS membership_name')
    					->where('users.name', $domain)
    					->where('users.password', '!=', null)
    					->where('users.active', 1)
    					->join('shops', 'shops.user_id', '=', 'users.id')
    					->join('ss_customers', 'ss_customers.shop_id', '=', 'shops.id')
    					->join('ss_contracts', 'ss_contracts.ss_customer_id', '=', 'ss_customers.id')
    					->join('ss_plans', 'ss_contracts.ss_plan_id', '=', 'ss_plans.id')
    					->where('ss_customers.email', $customerEmail)
    					->where('ss_customers.active', 1)
    					->get();

    		if(count($entity) <= 0){
				return response()->json(['message' => 'No results'], 422);
    		}
			return response()->json(['results' => CustomerApiResource::collection($entity)], 200);
    	}catch(\Exception $e){
    		logger("============= ERROR ::  validateCustomer =============");
			logger($e);
			return response()->json(['data' => $e->getMessage()], 422);
    	}
    }
}
