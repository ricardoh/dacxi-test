<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [];

        $data['currency'] = trans("dacxi.currencies.{$this->currency}");
        $data['currency_symbol'] = Str::upper($this->currency);
        $data['coin'] = trans("dacxi.coins.{$this->coin}");
        $data['coin_symbol'] = Str::upper($this->coin);
        $data['price'] = (double) $this->price * 1.0;
        $data['updated_at'] = $this->updated_at->toIso8601String();

        return $data;
    }
}
