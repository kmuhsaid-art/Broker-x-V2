<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'symbol' => ['required', 'string'],
            'side' => ['required', 'in:BUY,SELL'],
            'type' => ['required', 'in:MARKET,LIMIT,STOP,STOP_LIMIT'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'gt:0'],
        ]);

        $user = $request->user();
        $market = Market::where('symbol', $validated['symbol'])->firstOrFail();

        // Hitung estimasi margin yang dibutuhkan (contoh sederhana: quantity * price)
        // Untuk market order, gunakan harga pasar terbaru dari tabel markets/ticker
        $estimatedCost = $validated['quantity'] * ($validated['price'] ?? $market->last_price);

        // ✅ VALIDASI SALDO SEBELUM CREATE ORDER
        $wallet = $user->wallets()
            ->where('currency', 'USD') // Sesuaikan dengan base currency trading
            ->first();

        if (!$wallet || ($wallet->balance - $wallet->locked_balance) < $estimatedCost) {
            return response()->json([
                'message' => 'Insufficient available balance to place this order.',
                'data' => [
                    'available' => number_format($wallet?->balance - $wallet?->locked_balance ?? 0, 2),
                    'required' => number_format($estimatedCost, 2)
                ]
            ], 422);
        }

        // ✅ GUNAKAN DB TRANSACTION UNTUK KEAMANAN DATA
        try {
            $order = DB::transaction(function () use ($validated, $market, $user, $estimatedCost) {
                
                // 1. Kunci saldo di wallet
                $user->wallets()
                    ->where('currency', 'USD')
                    ->decrement('balance', $estimatedCost);
                    
                $user->wallets()
                    ->where('currency', 'USD')
                    ->increment('locked_balance', $estimatedCost);

                // 2. Buat record order
                return Order::create([
                    'user_id' => $user->id,
                    'market_id' => $market->id,
                    'side' => $validated['side'],
                    'type' => $validated['type'],
                    'price' => $validated['price'],
                    'quantity' => $validated['quantity'],
                    'remaining_quantity' => $validated['quantity'],
                    'status' => 'open',
                    'margin_locked' => $estimatedCost, // Simpan berapa saldo yang dikunci
                ]);
            });

            return response()->json($order, 201);

        } catch (\Exception $e) {
            // Jika gagal, transaction otomatis rollback (saldo kembali utuh)
            return response()->json([
                'message' => 'Failed to place order: ' . $e->getMessage()
            ], 500);
        }
    }
}
