<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function index()
    {
        // Hardcoded rates (nanti bisa ambil dari API eksternal seperti CoinGecko)
        return response()->json([
            'IDR' => 0.0000645,  // 1 IDR = 0.0000645 USD
            'USD' => 1,
            'USDT' => 1,
            'BTC' => 65000,
            'ETH' => 3500,
            'EUR' => 1.08,
            'SGD' => 0.74,
        ]);
    }
}

