<?php

namespace App\Services;

use App\Models\Pair;
use App\Models\Price;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class CoinGeckoService
{
    public static function getSimplePrices()
    {
        $currencies = collect(config('dacxi.currencies', []));
        $coins = collect(config('dacxi.coins', []))->flip();
        $prices = collect();

        $payload = [
            'ids' => $coins->keys()->implode(','),
            'vs_currencies' => $currencies->implode(','),
            'include_last_updated_at' => 'true'
        ];

        $response = Http::get('https://api.coingecko.com/api/v3/simple/price', $payload)
            ->json();

        foreach ($response as $coinCode => $coinPrices) {
            $updatedAt = $coinPrices['last_updated_at'];
            unset($coinPrices['last_updated_at']);

            $coin = $coins->get($coinCode);

            foreach ($coinPrices as $currency => $price) {
                $newPrice = new Price;
                $newPrice->currency = $currency;
                $newPrice->coin = $coin;
                $newPrice->price = $price;
                $newPrice->created_at = $updatedAt;
                $newPrice->updated_at = $updatedAt;

                $prices->push($newPrice);
            }
        }

        return $prices;
    }

    public static function getHistoricPrices(Pair $pair, Carbon $when)
    {
        $coinCode = config("dacxi.coins.{$pair->coin}");

        $payload = [
            'vs_currency' => $pair->currency,
            'from' => $when->copy()->subHours(3)->timestamp,
            'to' => $when->copy()->addHours(3)->timestamp,
        ];

        $response = Http::get("https://api.coingecko.com/api/v3/coins/{$coinCode}/market_chart/range", $payload)
            ->json();

        return collect($response['prices'])->map(function ($values) use ($pair, $when) {
            $price = new Price;

            $price->currency = $pair->currency;
            $price->coin = $pair->coin;
            $price->price = $values[1];
            $price->created_at = new Carbon($values[0] / 1000);
            $price->updated_at = $price->created_at->copy();

            return $price;
        });
    }
}
