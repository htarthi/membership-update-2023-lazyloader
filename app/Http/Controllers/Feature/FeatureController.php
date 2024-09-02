<?php

namespace App\Http\Controllers\Feature;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ShopifyTrait;
use App\Models\Feature;
use App\Models\Featurables;
class FeatureController extends Controller
{
    use ShopifyTrait;

    public function add($name)
    {
        try {
            logger("============= SUCCESS ::  Feature Added Successfully =============");
            $feature = Feature::create([
                'name' => $name,
                'is_enabled' => false,
            ]);
            return $feature;
        } catch (\Exception $e) {
            logger("============= ERROR ::  add =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function enableFor($name,$id)
    {
        try {
            logger("============= SUCCESS ::  Feature Added Successfully =============");
            $user = User::find($id);
            $feature = Feature::where('name',$name)->first();
            if($feature){
                $feature->is_enabled = true;
                $feature->save();
                Featurables::create([
                    'feature_id' => $feature->id,
                    'featurable_id' => $user->id,
                    'featurable_type' => "App\Models\User",
                ]);
            }
            return 'Feature <b>' . $name . '</b> is enable for user <b>"' . $user->name . '"</b>';
        } catch (\Exception $e) {
            logger("============= ERROR ::  enableFor =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function disableFor($name, $id)
    {
        try {
            $user = User::find($id);
            $feature = Feature::where('name',$name)->first();
            if($feature){
                Featurables::where([
                    'feature_id' => $feature->id,
                    'featurable_id' => $user->id,
                    'featurable_type' => "App\Models\User"])->delete();
            }
            return 'Feature <b>' . $name . '</b> is disable for user <b>"' . $user->name . '"</b>';
        } catch (\Exception $e) {
            logger("============= ERROR ::  disableFor =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function remove($name, $id)
    {
        try {
            Feature::add('my.feature', true);
        } catch (\Exception $e) {
            logger("============= ERROR ::  remove =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function createCustomPlan($user_id, $plan_id)
    {
        try {
            return $this->addCustomPlan($user_id, $plan_id);
        } catch (\Exception $e) {
            logger("============= ERROR ::  createCustomPlan =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }
}
