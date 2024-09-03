<?php

namespace App\Http\Controllers\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SsContract;
use App\Models\SsContractLineItem;
use App\Models\SsPlan;
use App\Traits\ShopifyTrait;
use App\Jobs\PriceChangeForSubscriptionContractJob;
use App\Traits\ImageTrait;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Translation\LoggingTranslator;

class UpdateContractController extends Controller
{
    use ShopifyTrait;
    public function index(Request $request)
    {
        try {
            $migrateContrats = [2546532518];

            $db_sellingPlan = SsPlan::find(93);

            $data['lineitem']['newVariantID'] = 39712834453670;
            $data['lineitem']['newProductId'] = 6126768947366;
            $data['lineitem']['sku'] = '';
            $data['lineitem']['title'] = 'Test product';
            $data['lineitem']['shopify_variant_image'] = 'https://cdn.shopify.com/s/files/1/0478/3027/0118/products/product-5-v2.png?v=1624351950';
            $data['lineitem']['shopify_variant_title'] = 'M / Black';
            $data['lineitem']['selling_plan_id'] = $db_sellingPlan->shopify_plan_id;
            $data['lineitem']['selling_plan_name'] = $db_sellingPlan->name;


            $data['new_plan_group_id'] = 59;
            $data['new_plan_id'] = 93;

            foreach ($migrateContrats as $migrateContractID) {

                $contract = SsContract::where('shopify_contract_id', $migrateContractID)->first();
                $db_lineitem = SsContractLineItem::where('shopify_contract_id', $migrateContractID)->first();


                $res = $this->contractLineUpdate($migrateContractID, $data, $db_lineitem, $contract->user_id);
                if ($res == 'success') {
                    $contract->ss_plan_groups_id = $data['new_plan_group_id'];
                    $contract->ss_plan_id = $data['new_plan_id'];

                    $contract->save();

                    $db_lineitem->shopify_product_id = $data['lineitem']['newProductId'];
                    $db_lineitem->shopify_variant_id = $data['lineitem']['newVariantID'];
                    $db_lineitem->sku = $data['lineitem']['sku'];
                    $db_lineitem->title = $data['lineitem']['title'];
                    $db_lineitem->shopify_variant_image = $data['lineitem']['shopify_variant_image'];
                    $db_lineitem->shopify_variant_title = $data['lineitem']['shopify_variant_title'];
                    $db_lineitem->selling_plan_id = $data['lineitem']['selling_plan_id'];
                    $db_lineitem->selling_plan_name = $data['lineitem']['selling_plan_name'];

                    $db_lineitem->save();
                }
            }

            return response()->json(['data' => []], 200);
        } catch (\Exception $e) {
            logger("============= ERROR ::  index =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function contractLineUpdate($sh_contract_id, $data, $lineItem, $user_id)
    {
        try {
            $draftId = $this->getSubscriptionDraft($user_id, $sh_contract_id);

            if ($draftId) {
                $query = '
                 mutation{
                      subscriptionDraftLineUpdate(
                            draftId: "' . $draftId . '",
                            input: {
                                productVariantId: "gid://shopify/ProductVariant/' . $data['lineitem']['newVariantID'] . '",
                            },
                            lineId: "gid://shopify/SubscriptionLine/' . $lineItem->shopify_line_id . '"
                        ){
                            userErrors {
                              code
                              field
                              message
                            }
                        }
                    }';

                $subscriptionDraftResult = $this->graphQLRequest($user_id, $query);
                $message = $this->getReturnMessage($subscriptionDraftResult, 'subscriptionDraftLineUpdate');

                if ($message == 'success') {
                    $message = $this->commitDraft($user_id, $draftId);
                    return $message;
                }

                return $message;
            }
        } catch (\Exception $e) {
            logger("============= ERROR ::  contractLineUpdate =============");
            logger($e);
            return response()->json(['data' => $e->getMessage()], 422);
        }
    }

    public function updatePriceForSC2(Request $request)
    {
        try {
            $d['isSuccess'] = true;
            $user = \Auth::user();
            //$file_path = Storage::disk('public')->path('uploads/csv_file/PriceUpdate.csv');
            $res = $this->checkIsPriceUpdateCSV($request->file);
            //$res = $this->checkIsPriceUpdateCSV($file_path);
            // logger($res);

            if (!$res['isSuccess']) {
                return response()->json(['data' => $res], 200);
            } else {
                $file = ImageTrait::makeImage($request->file, 'uploads/csv_file/');
                $file_path = Storage::disk('public')->path('uploads/csv_file/' . $file);
                PriceChangeForSubscriptionContractJob::dispatch($file_path, $user->id)->onQueue('UpdateServer');
            }

            $d['data'] = 'Subscription Contract Price updating in background, You will receive an email once this job will done';
            $d['isSuccess'] = true;


            return response()->json(['data' => $d], ($d['isSuccess']) ? 200 : 422);
        } catch (\Exception $e) {
            logger("============= ERROR ::  updatePriceForSC =============");
            logger($e);
            Bugsnag::notifyException($e);
            return response()->json(['data' => $e], 422);
        }
    }
}
