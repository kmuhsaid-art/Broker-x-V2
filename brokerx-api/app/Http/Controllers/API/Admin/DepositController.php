<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    public function index()
    {
        return Deposit::with([
            'user',
            'wallet.asset',
            'paymentMethod',
            'paymentAccount'
        ])
        ->latest()
        ->get();
    }

    public function show(Deposit $deposit)
    {
        return $deposit->load([
            'user',
            'wallet.asset',
            'paymentMethod',
            'paymentAccount'
        ]);
    }

    public function approve(Request $request, Deposit $deposit)
{
    if ($deposit->status !== 'PENDING') {
        return response()->json([
            'message' => 'Deposit already processed.'
        ], 422);
    }

    // 1. Update status deposit
    $deposit->update([
        'status' => 'SUCCESS', // Ubah dari APPROVED ke SUCCESS
        'approved_by' => $request->user()->id,
        'approved_at' => now(),
        'admin_notes' => $request->admin_notes,
    ]);

    // 2. TAMBAHKAN SALDO KE WALLET USER
    $wallet = \App\Models\Wallet::find($deposit->wallet_id);
    if ($wallet) {
        $wallet->balance += $deposit->amount;
        $wallet->save();
    } else {
        return response()->json([
            'message' => 'Wallet not found for this deposit.'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Deposit approved and balance updated.',
        'data' => $deposit->load(['wallet', 'user'])
    ]);
}
 public function approve(Request $request, Deposit $deposit)
{
    if ($deposit->status !== 'PENDING') {
        return response()->json([
            'message' => 'Deposit already processed.'
        ], 422);
    }

    // 1. Update status deposit
    $deposit->update([
        'status' => 'SUCCESS', // Ubah dari APPROVED ke SUCCESS
        'approved_by' => $request->user()->id,
        'approved_at' => now(),
        'admin_notes' => $request->admin_notes,
    ]);

    // 2. TAMBAHKAN SALDO KE WALLET USER
    $wallet = \App\Models\Wallet::find($deposit->wallet_id);
    if ($wallet) {
        $wallet->balance += $deposit->amount;
        $wallet->save();
    } else {
        return response()->json([
            'message' => 'Wallet not found for this deposit.'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'Deposit approved and balance updated.',
        'data' => $deposit->load(['wallet', 'user'])
    ]);
}

    public function reject(Request $request, Deposit $deposit)
    {
        if ($deposit->status !== 'PENDING') {
            return response()->json([
                'message' => 'Deposit already processed.'
            ], 422);
        }

        $deposit->update([
            'status' => 'REJECTED',
            'rejected_by' => $request->user()->id,
            'rejected_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Deposit rejected.',
            'data' => $deposit
        ]);
    }
}
