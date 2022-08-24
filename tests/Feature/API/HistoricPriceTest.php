<?php

namespace Tests\Feature\API;

use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class HistoricPriceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    function it_returns_an_estimated_price_at_datetime_from_database()
    {
        Http::fake();

        Price::factory()
            ->count(3)
            ->state(new Sequence(
                ['created_at' => '2022-08-18 12:00:00', 'updated_at' => '2022-08-18 12:00:00'],
                ['created_at' => '2022-08-18 13:00:00', 'updated_at' => '2022-08-18 13:00:00'],
                ['created_at' => '2022-08-18 14:00:00', 'updated_at' => '2022-08-18 14:00:00'],
            ))
            ->create();

        $response = $this->getJson('/api/history-price?date=2022-08-18&time=13:05:00');
        $response->assertSuccessful();
        $response->assertJsonPath('data.currency', 'United States Dollar');
        $response->assertJsonPath('data.currency_symbol', 'USD');
        $response->assertJsonPath('data.coin', 'Bitcoin');
        $response->assertJsonPath('data.coin_symbol', 'BTC');
        $response->assertJsonPath('data.price', 1234);
        $response->assertJsonPath('data.updated_at', '2022-08-18T13:00:00+00:00');

        Http::assertNothingSent();
    }

    /** @test */
    function it_returns_an_estimated_price_at_datetime_from_api()
    {
        Http::fake(function (Request $request) {
            return Http::response('{"prices":[[1659974406971,23988.969591535944],[1659978139350,23947.13668905716],[1659981634058,23979.964200696733],[1659985308418,23948.652880092963],[1659988847428,23966.380191756194],[1659992517601,24077.6213212528]],"market_caps":[[1659974406971,458121065537.90063],[1659978139350,457501286751.31885],[1659981634058,458279746703.2171],[1659985308418,458370887998.7311],[1659988847428,456833395632.94415],[1659992517601,460273371107.59375]],"total_volumes":[[1659974406971,25119903547.056973],[1659978139350,25755410199.19216],[1659981634058,26065351078.53063],[1659985308418,25902500623.567135],[1659988847428,26310837172.84936],[1659992517601,26814268331.27832]]}', 200);
        });

        $response = $this->getJson('/api/history-price?date=2022-08-08&time=18:10:00');
        $response->assertSuccessful();
        $response->assertJsonPath('data.currency', 'United States Dollar');
        $response->assertJsonPath('data.currency_symbol', 'USD');
        $response->assertJsonPath('data.coin', 'Bitcoin');
        $response->assertJsonPath('data.coin_symbol', 'BTC');
        $response->assertJsonPath('data.price', 23979.964200696733);
        $response->assertJsonPath('data.updated_at', '2022-08-08T18:00:34+00:00');

        $this->assertEquals(6, Price::count());

        Http::assertSentCount(1);
    }

    /** @test */
    function it_requires_date()
    {
        Price::factory()->knownDate()->create();

        $response = $this->getJson('/api/history-price');
        $response->assertStatus(422);
        $response->assertInvalid(['date']);
    }

    /** @test */
    function it_rejects_invalid_coin()
    {
        Price::factory()->create();

        $response = $this->getJson('/api/history-price?date=2022-08-18&coin=invalid');
        $response->assertStatus(422);
        $response->assertInvalid(['coin']);
    }

    /** @test */
    function it_rejects_invalid_currency()
    {
        Price::factory()->create();

        $response = $this->getJson('/api/history-price?date=2022-08-18&currency=invalid');
        $response->assertStatus(422);
        $response->assertInvalid(['currency']);
    }

    /** @test */
    function it_rejects_invalid_time()
    {
        Price::factory()->create();

        $response = $this->getJson('/api/history-price?date=2022-08-18&time=invalid');
        $response->assertStatus(422);
        $response->assertInvalid(['time']);
    }

    /**
     * @test
     * @dataProvider validCoinsProvider
     */
    public function it_accepts_a_valid_coin($coin)
    {
        Http::fake();

        Price::factory()
            ->count(3)
            ->state(new Sequence(
                ['created_at' => '2022-08-18 12:00:00', 'updated_at' => '2022-08-18 12:00:00'],
                ['created_at' => '2022-08-18 13:00:00', 'updated_at' => '2022-08-18 13:00:00'],
                ['created_at' => '2022-08-18 14:00:00', 'updated_at' => '2022-08-18 14:00:00'],
            ))
            ->create([
                'coin' => $coin,
            ]);

        $response = $this->getJson("/api/history-price?coin={$coin}&date=2022-08-18&time=13:05:00");
        $response->assertSuccessful();
        $response->assertJsonPath('data.currency', 'United States Dollar');
        $response->assertJsonPath('data.currency_symbol', 'USD');
        $response->assertJsonPath('data.coin', trans("dacxi.coins.{$coin}"));
        $response->assertJsonPath('data.coin_symbol', Str::upper($coin));
        $response->assertJsonPath('data.price', 1234);
        $response->assertJsonPath('data.updated_at', '2022-08-18T13:00:00+00:00');

        Http::assertNothingSent();
    }

    /**
     * @test
     * @dataProvider validCurrencyProvider
     */
    public function it_accepts_a_valid_currency($currency)
    {
        Http::fake();

        Price::factory()
            ->count(3)
            ->state(new Sequence(
                ['created_at' => '2022-08-18 12:00:00', 'updated_at' => '2022-08-18 12:00:00'],
                ['created_at' => '2022-08-18 13:00:00', 'updated_at' => '2022-08-18 13:00:00'],
                ['created_at' => '2022-08-18 14:00:00', 'updated_at' => '2022-08-18 14:00:00'],
            ))
            ->create([
                'currency' => $currency,
            ]);

        $response = $this->getJson("/api/history-price?currency={$currency}&date=2022-08-18&time=13:05:00");
        $response->assertSuccessful();
        $response->assertJsonPath('data.currency', trans("dacxi.currencies.{$currency}"));
        $response->assertJsonPath('data.currency_symbol', Str::upper($currency));
        $response->assertJsonPath('data.coin', 'Bitcoin');
        $response->assertJsonPath('data.coin_symbol', 'BTC');
        $response->assertJsonPath('data.price', 1234);
        $response->assertJsonPath('data.updated_at', '2022-08-18T13:00:00+00:00');

        Http::assertNothingSent();
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
