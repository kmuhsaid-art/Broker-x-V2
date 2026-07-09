<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DepositController extends Controller
{
    public function index(Request $request)
    {
        return Deposit::with([
            'wallet.asset',
            'paymentMethod',
            'paymentAccount'
        ])
        ->where('user_id', $request->user()->id)
        ->latest()
        ->get();
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'amount' => 'required|numeric|min:10',
        'currency' => 'required|string|in:USD,IDR', // User memilih mata uang
        'payment_account_id' => 'nullable|exists:payment_accounts,id',
        'proof' => 'nullable|image|max:2048',
    ]);

    $user = $request->user();

    // ✅ CARI WALLET_ID BERDASARKAN USER & CURRENCY
    $wallet = $user->wallets()->where('currency', $validated['currency'])->first();

    if (!$wallet) {
        return response()->json([
            'message' => "Wallet for currency {$validated['currency']} not found. Please contact support."
        ], 404);
    }

    $deposit = DB::transaction(function () use ($validated, $user, $wallet) {
        return $user->deposits()->create([
            'uuid' => Str::uuid(), // Generate UUID unik
            'wallet_id' => $wallet->id, // ✅ SIMPAN WALLET_ID DI SINI
            'payment_account_id' => $validated['payment_account_id'],
            'amount' => $validated['amount'],
            'net_amount' => $validated['amount'], // Fee default 0 saat request
            'status' => 'PENDING',
            'reference' => 'DEP-' . strtoupper(uniqid()),
        ]);
    });

    // Upload bukti transfer jika ada
    if ($request->hasFile('proof')) {
        $path = $request->file('proof')->store('deposits/proofs');
        $deposit->update(['proof' => $path]);
    }

    return response()->json([
        'message' => 'Deposit request submitted successfully.',
        'data' => $deposit
    ], 201);
}

    public function show(Deposit $deposit)
    {
        return $deposit->load([
            'wallet.asset',
            'paymentMethod',
            'paymentAccount'
        ]);
    }
}
