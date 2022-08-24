<?php

namespace App\Http\Controllers;

use App\Http\Requests\HistoryRequest;
use App\Http\Resources\PriceResource;
use App\Models\Pair;
use App\Models\Price;
use App\Services\CoinGeckoService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class HistoryController extends Controller
{
    /**
     * Return estimated coin price at date and time (default for time is 00:00)
     *
     * @param  \App\Http\Requests\HistoryRequest  $request
     * @return \App\Http\Resources\PriceResource
     */
    public function __invoke(HistoryRequest $request)
    {
        $price = Price::pair($request->pair())
            ->closestToDate($request->datetime())
            ->first();

        if (is_null($price)) {
            $price = $this->fetchHistoricPrice(
                $request->pair(),
                $request->datetime()
            );
        }

        abort_if(is_null($price), 404);

        return new PriceResource($price);
    }

    /**
     * Fetch the historic price for the pair (currency & coin), stores it in the
     * database and returns the closest result to provived date/time.
     *
     * @param  \App\Models\Pair $pair
     * @param  \Illuminate\Support\Carbon  $when
     * @return \App\Models\Price
     */
    private function fetchHistoricPrice(Pair $pair, Carbon $when)
    {
        $prices = CoinGeckoService::getHistoricPrices($pair, $when);

        return $prices->map(function ($price) use ($when) {
            $price->difference = $when->diffInSeconds($price->updated_at, true);

            return $price;
        })->sortBy('difference')->each(function ($price) {
            try {
                unset($price->difference);

                $price->save();
            } catch (\Exception $e) {
            }
        })->first();
    }
}
