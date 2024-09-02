<?php

namespace App\Imports;

use App\Traits\ShopifyTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MigrateMembershipImport implements ToModel, WithHeadingRow
{
    use ShopifyTrait;

    private $user_id;

    public function __construct($user_id)
    {
        Log::info('metafieldImport');
        $this->user_id = $user_id;
    }

    public function model(array $row)
    {
        if (($row['gateway_customer_id'] && $row['gateway_customer_id'] != '') || $row['shopify_payment_method_id'] && $row['shopify_payment_method_id'] != '') {
            logger('----------------- MigrateMembershipImport Strted ----------------------');
            $shopify_customer_id = '';
            // create customer if missing
            if ($row['customer_shopify_id'] == '' || $row['customer_shopify_id'] == null) {
                $shopify_customer_id = $this->createUpdateCustomer($row, $this->user_id);
            } else {
                $tempRes = $this->createUpdateCustomer($row, $this->user_id);
                $shopify_customer_id = $row['customer_shopify_id'];
            }

            logger('Shoify customer Id ::: ' . $shopify_customer_id);

            if ($shopify_customer_id != '') {
                logger('shopify_customer_id exist.');

                $row['customer_shopify_id'] = $shopify_customer_id;

                if ($row['shopify_payment_method_id'] && $row['shopify_payment_method_id'] != '') {
                    $row['paymentMethodId'] = 'gid://shopify/CustomerPaymentMethod/' . $row['shopify_payment_method_id'];
                    logger(' --------------------- PaymentMethodId ' . $row['paymentMethodId']);
                } else {
                    // create customer payment method
                    $paymentMethodIdResult = $this->createCustomerPaymentMethod($row, $this->user_id);

                    logger(json_encode($paymentMethodIdResult));
                    if (!$paymentMethodIdResult['success']) {
                        logger('paymentMethodIdResult :: ' . json_encode($paymentMethodIdResult));
                        return $paymentMethodIdResult;
                    }

                    $row['paymentMethodId'] = $paymentMethodIdResult['message'];
                }

                // $row['paymentMethodId'] = 'gid://shopify/CustomerPaymentMethod/d890f0d7275aa527c84af88884ed44e';

                $ContractDraftIdResult = $this->createSubscriptionContractInShopify($row, $this->user_id, true);

                if (!$ContractDraftIdResult['success'] || $ContractDraftIdResult['message'] == '') {
                    // return false;
                    // return response()->json(['data' => $ContractDraftIdResult], 200);
                } else {
                    $lineItem['price'] = $row['line_item_price'];
                    $lineItem['discount_type'] = '';
                    $lineItem['discount_amount'] = 0;
                    $lineItem['final_amount'] = $row['line_item_price'];
                    $lineItem['shopify_variant_id'] = $row['line_item_variant_id'];
                    $lineItem['quantity'] = $row['line_item_qty'];

                    $contractLineAddResult = $this->subscriptionDraftLineAdd($this->user_id, $lineItem, $ContractDraftIdResult['message']);

                    logger("========================= contractLineAddResult ========================");
                    logger($contractLineAddResult);

                    $shopify_contract_id = str_replace('gid://shopify/SubscriptionContract/', '', $contractLineAddResult['contractID']);
                    // $mBillingAttempt = $this->createBillingAttemptAfterMigration($shopify_contract_id, $this->user_id);
                    //    if($mBillingAttempt['isSuccess']){
                    //        // $data->origin_order_id = $mBillingAttempt['order_id'];
                    //    }
                    // $res = $this->commitDraft($user->id, $ContractDraftIdResult['message']);

                    // dd($contractLineAddResult);
                    // $this->createSubscriptionContractInShopify($row, $this->user_id);
                    // TODO: Implement model() method.

                }
            }
            logger('============================= Ended :: Migration ====================================');
        }
    }
}
