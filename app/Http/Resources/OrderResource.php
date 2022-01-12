<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'            =>  $this->id,
            'sold_to'       =>  $this->user->email,
            'sold_product'  =>  $this->product->name,
            'sold_quantity' =>  $this->quantity,
            'purchased_at'  =>  $this->created_at->toDateTimeString()
        ];
    }
}
