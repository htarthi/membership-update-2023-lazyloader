<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\ShopifyTrait;

class SubscriptionResource extends JsonResource
{
    use ShopifyTrait;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->ss_customer_id,
            'status' => $this->status,
            'status_display' => $this->status_display,
            'status_billing' => $this->status_billing,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'order_count' => $this->order_count,
            'phone' => $this->phone,
            'email' => $this->email,
            'plan_name' => $this->plan_name,
            'is_migrated' => $this->is_migrated,
            'is_onetime_payment' => $this->is_onetime_payment,
            'member_number'=>$this->member_number,
            'last_payment_status' => ucfirst($this->last_payment_status),
            'date_first_order' => date('M d, Y', strtotime($this->getSubscriptionTimeDate(date('Y-m-d', strtotime($this->created_at)), $this->shop_id, date('H:i:s', strtotime($this->created_at))))),
            'next_order_date' => date('M d, Y', strtotime($this->getSubscriptionTimeDate(date('Y-m-d', strtotime($this->next_order_date)), $this->shop_id, date('H:i:s', strtotime($this->next_order_date))))),
            'next_processing_date'=> date('M d, Y', strtotime($this->getSubscriptionTimeDate(date('Y-m-d', strtotime($this->next_processing_date)), $this->shop_id, date('H:i:s', strtotime($this->next_processing_date)))))
        ];
    }
}
