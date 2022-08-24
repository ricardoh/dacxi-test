<?php

namespace App\Models;

class Pair
{
    public function __construct(
        public string $currency,
        public string $coin
    ) {
        //
    }
}
