<?php

namespace App\Http\Resources\Api\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_name' => $this->product_name,
            'amount' => $this->amount,
            'status' => $this->status,
            'created_at' => Carbon::create($this->created_at)->toDateTimeString(),
            'updated_at' => Carbon::create($this->updated_at)->toDateTimeString(),
        ];
    }
}
