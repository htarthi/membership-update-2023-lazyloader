<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class CustomerMetafiedsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $userId;
    public $data;
    /**
     * Create a new job instance.
     */
    public function __construct($userId,$data)
    {
        $this->userId = $userId;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        logger("========================================== START :: CustomerMetafiedsJob 11=======================");
        $user = User::where('id', $this->userId)->first();
        if(isset($this->data['tags'])){
            $getTag = $this->data['tags'] ;
            $getCustId = $this->data['id'] ;
            if($getCustId){
                $getCustomer = $user->api()->rest('GET', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/'.$getCustId.'/metafields.json');
                if(!$getCustomer['errors']){
                    $check = isset($getCustomer['body']) ? $getCustomer['body']['container']['metafields'] : '';
                    if($check){
                        $existMetaFields = $getCustomer['body']['container']['metafields'];
                        foreach($existMetaFields  as $metafields){
                            if($metafields['key'] == 'customer-discount-tags'){
                                $getVal = json_decode($metafields['value'], true);
                                $originalTag = explode(',',$this->data['tags']);
                                $tagsArray = explode(',', $getVal['tags']);
                                $processArray = function($array) {
                                    return array_values(array_unique(array_map('trim', $array)));
                                };
                                $processedArray1 = $processArray($originalTag);
                                $processedArray2 = $processArray($tagsArray);
                                $areEqual = ($processedArray1 === $processedArray2);
                                if(!$areEqual){
                                    $formattedStr = preg_replace('/\s*,\s*/', ',', $getTag);
                                    $keyVals['tags'] = $formattedStr;
                                    $parameter = [
                                        "metafield" => [
                                            'namespace' => 'simplee',
                                            'key' => 'customer-discount-tags',
                                            'value' => json_encode($keyVals),
                                            'type' => 'json'
                                        ]
                                    ];
                                    $output = $user->api()->rest('POST', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/'.$getCustId.'/metafields.json', $parameter);
                                    // logger("EXIST UPDATE");
                                }else{
                                    // logger("NOT UPDATE");
                                }
                            }
                        }
                    }else{
                        // logger("NEW ONE");
                        if($getTag){
                            $formattedStr = preg_replace('/\s*,\s*/', ',', $getTag);
                            $keyVals['tags'] = $formattedStr;
                            $parameter = [
                                "metafield" => [
                                    'namespace' => 'simplee',
                                    'key' => 'customer-discount-tags',
                                    'value' => json_encode($keyVals),
                                    'type' => 'json'
                                ]
                            ];
                            $user->api()->rest('POST', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/customers/'.$getCustId.'/metafields.json', $parameter);
                        }
                    }
                }
            }
        }
        logger("========================================= END :: CustomerMetafiedsJob=======================");
    }
}
