<?php

namespace App\Http\Controllers;

use App\Http\Requests\LatestRequest;
use App\Http\Resources\PriceResource;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class LatestController extends Controller
{
    /**
     * Return the most recent coin price.
     *
     * @param  \App\Http\Requests\LatestRequest  $request
     * @return \App\Http\Resources\PriceResource
     */
    public function __invoke(LatestRequest $request)
    {
        $price = Price::pair($request->pair())
            ->latest()
            ->first();

        abort_if(is_null($price), 404);

        return new PriceResource($price);
    }
}
