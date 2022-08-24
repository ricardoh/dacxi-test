<?php

namespace App\Console\Commands;

use App\Services\CoinGeckoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateCryptoPricesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update crypto prices';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $prices = CoinGeckoService::getSimplePrices();

        $prices->each(function ($price) {
            try {
                $price->save();

                $this->info("Imported {$price->currency}/{$price->coin}");
            } catch (\Exception $e) {
                $this->warn("Skipped {$price->currency}/{$price->coin}");
            }
        });

        return 0;
    }
}
