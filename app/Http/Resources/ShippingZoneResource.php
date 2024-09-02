<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShippingZoneResource extends JsonResource
{
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
            'zone_name' => $this->zone_name,
            'zone_country' => json_decode($this->countries),
            'rate_name' => $this->rate_name,
            'rate_value' => $this->rate_value,
        ];
    }
}
