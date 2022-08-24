<?php

namespace Tests\Feature\API;

use App\Models\Price;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class LatestPriceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_returns_latest_bitcoin_price()
    {
        Price::factory()->knownDate()->create();

        $response = $this->getJson('/api/latest-price');
        $response->assertSuccessful();
        $response->assertJsonPath('data.currency', 'United States Dollar');
        $response->assertJsonPath('data.currency_symbol', 'USD');
        $response->assertJsonPath('data.coin', 'Bitcoin');
        $response->assertJsonPath('data.coin_symbol', 'BTC');
        $response->assertJsonPath('data.price', 1234);
        $response->assertJsonPath('data.updated_at', '2022-08-18T12:00:00+00:00');
    }

    /** @test */
    function it_rejects_invalid_coin()
    {
        Price::factory()->create();

        $response = $this->getJson('/api/latest-price?coin=invalid');
        $response->assertStatus(422);
        $response->assertInvalid(['coin']);
    }

    /** @test */
    function it_rejects_invalid_currency()
    {
        Price::factory()->create();

        $response = $this->getJson('/api/latest-price?currency=invalid');
        $response->assertStatus(422);
        $response->assertInvalid(['currency']);
    }

    /** @test */
    function it_rejects_invalid_coin_and_currency()
    {
        Price::factory()->create();

        $response = $this->getJson('/api/latest-price?coin=invalid&currency=invalid');
        $response->assertStatus(422);
        $response->assertInvalid(['coin', 'currency']);
    }

    /**
     * @test
     * @dataProvider validCoinsProvider
     */
    public function it_accepts_a_valid_coin($coin)
    {
        Price::factory()->knownDate()->create([
            'coin' => $coin,
        ]);

        $response = $this->getJson("/api/latest-price?coin={$coin}");
        $response->assertSuccessful();
        $response->assertJsonPath('data.currency', 'United States Dollar');
        $response->assertJsonPath('data.currency_symbol', 'USD');
        $response->assertJsonPath('data.coin', trans("dacxi.coins.{$coin}"));
        $response->assertJsonPath('data.coin_symbol', Str::upper($coin));
        $response->assertJsonPath('data.price', 1234);
        $response->assertJsonPath('data.updated_at', '2022-08-18T12:00:00+00:00');
    }

    /**
     * @test
     * @dataProvider validCurrencyProvider
     */
    public function it_accepts_a_valid_currency($currency)
    {
        Price::factory()->knownDate()->create([
            'currency' => $currency,
        ]);

        $response = $this->getJson("/api/latest-price?currency={$currency}");
        $response->assertSuccessful();
        $response->assertJsonPath('data.currency', trans("dacxi.currencies.{$currency}"));
        $response->assertJsonPath('data.currency_symbol', Str::upper($currency));
        $response->assertJsonPath('data.coin', 'Bitcoin');
        $response->assertJsonPath('data.coin_symbol', 'BTC');
        $response->assertJsonPath('data.price', 1234);
        $response->assertJsonPath('data.updated_at', '2022-08-18T12:00:00+00:00');
    }

    /** @test */
    function it_returns_404_if_there_is_no_price_in_database()
    {
        $response = $this->getJson("/api/latest-price");
        $response->assertStatus(404);
    }

    public function validCoinsProvider()
    {
        return [
            [ 'btc' ],
            [ 'dacxi' ],
            [ 'eth' ],
            [ 'atom' ],
            [ 'luna' ],
        ];
    }

    public function validCurrencyProvider()
    {
        return [
            [ 'usd' ],
            [ 'aud' ],
            [ 'brl' ],
        ];
    }
}
