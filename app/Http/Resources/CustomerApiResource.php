<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $next_billing_date = strtotime($this->next_processing_date);
        $current_date = strtotime(date('Y-m-d H:i:s'));

        return [
            'valid' => (($this->contract_status == 'active') || ($this->contract_status == 'cancelled' && $next_billing_date > $current_date)) ? true : false,
            'status' => $this->contract_status,
            'next_billing_date' => $this->next_processing_date,
            'membership' => $this->membership_name
        ];
    }
}
