<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\DepositController;
use App\Http\Controllers\API\MarketController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentAccountController as UserPaymentAccountController;
use App\Http\Controllers\API\PaymentMethodController as UserPaymentMethodController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ExchangeRateController;
use App\Http\Controllers\API\Admin\DepositController as AdminDepositController;
use App\Http\Controllers\API\Admin\PaymentAccountController as AdminPaymentAccountController;
use App\Http\Controllers\API\Admin\PaymentMethodController as AdminPaymentMethodController;
use App\Http\Controllers\API\WithdrawalController;
/*
|--------------------------------------------------------------------------
| Public Authentication
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Public Market & Data
|--------------------------------------------------------------------------
*/
Route::prefix('markets')->group(function () {
    Route::get('/', [MarketController::class, 'index']);
    Route::get('/ticker', [MarketController::class, 'ticker']);
    Route::get('/{symbol}/orderbook', [MarketController::class, 'orderBook']);
    Route::get('/{symbol}/candles', [MarketController::class, 'candles']);
    Route::get('/{symbol}', [MarketController::class, 'show']);
});

Route::get('/wallets', [WalletController::class, 'index']);
Route::get('/payment-methods', [UserPaymentMethodController::class, 'index']);
Route::get('/payment-accounts', [UserPaymentAccountController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes (USER)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // Profile & Auth
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'profile']);

    // Dashboard & Wallets
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/wallets', [WalletController::class, 'index']);
    
    // Deposits
    Route::get('/deposits', [DepositController::class, 'index']);
    Route::post('/deposits', [DepositController::class, 'store']);
    Route::get('/deposits/{deposit}', [DepositController::class, 'show']);
    
    // Withdrawal
    Route::get('/wthdrawals', [WithdrawalController::class, 'index']);
    Route::post('/wthdrawals', [WithdrawalController::class, 'store']);

    // Orders & Trading (Placeholder GET untuk menghindari error 405)
    Route::get('/orders', function () { return response()->json([]); });
    Route::get('/positions', function () { return response()->json([]); });
    Route::get('/trades', function () { return response()->json([]); });
    Route::post('/orders', [OrderController::class, 'store']);

    // Exchange Rates
    Route::get('/exchange-rates', [ExchangeRateController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | USER Investment Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/investment-products', [\App\Http\Controllers\API\InvestmentController::class,'products']);
    Route::get('/investment-products/{investmentProduct}', [\App\Http\Controllers\API\InvestmentController::class,'showProduct']);
    
    Route::get('/investments', [\App\Http\Controllers\API\InvestmentController::class,'myInvestments']);
    Route::post('/investments', [\App\Http\Controllers\API\InvestmentController::class,'store']);
    Route::get('/investments/{investmentOrder}', [\App\Http\Controllers\API\InvestmentController::class,'show']);
    
    Route::post('/investment-products/{product}/invest', [\App\Http\Controllers\API\InvestmentController::class,'invest']);
    Route::post('/investments/{investment}/cancel', [\App\Http\Controllers\API\InvestmentController::class,'cancel']);

}); // <--- PENUTUP GROUP AUTH:SANCTUM (USER)

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    
    Route::apiResource('payment-methods', AdminPaymentMethodController::class);
    Route::apiResource('payment-accounts', AdminPaymentAccountController::class);
    
    Route::get('/deposits', [AdminDepositController::class, 'index']);
    Route::get('/deposits/{deposit}', [AdminDepositController::class, 'show']);
    Route::post('/deposits/{deposit}/approve', [AdminDepositController::class, 'approve']);
    Route::post('/deposits/{deposit}/reject', [AdminDepositController::class, 'reject']);

    /*
    |--------------------------------------------------------------------------
    | ADMIN Investment Routes (TERPISAH DARI USER)
    |--------------------------------------------------------------------------
    */
    Route::apiResource('investment-categories', \App\Http\Controllers\API\Admin\InvestmentCategoryController::class);
    Route::apiResource('investment-products', \App\Http\Controllers\API\Admin\InvestmentProductController::class);
    
    Route::get('/investments/dashboard', [\App\Http\Controllers\API\Admin\AdminInvestmentController::class, 'dashboard']);
    Route::get('/investments', [\App\Http\Controllers\API\Admin\AdminInvestmentController::class, 'index']);
    Route::get('/investments/pending', [\App\Http\Controllers\API\Admin\AdminInvestmentController::class, 'pending']);
    Route::get('/investments/active', [\App\Http\Controllers\API\Admin\AdminInvestmentController::class, 'active']);
    Route::get('/investments/completed', [\App\Http\Controllers\API\Admin\AdminInvestmentController::class, 'completed']);
    Route::get('/investments/rejected', [\App\Http\Controllers\API\Admin\AdminInvestmentController::class, 'rejected']);
    Route::get('/investments/cancelled', [\App\Http\Controllers\API\Admin\AdminInvestmentController::class, 'cancelled']);
    Route::get('/investments/{investment}', [\App\Http\Controllers\API\Admin\AdminInvestmentController::class, 'show']);
    
    Route::post('/investments/{investment}/approve', [\App\Http\Controllers\API\Admin\AdminInvestmentController::class, 'approve']);
    Route::post('/investments/{investment}/reject', [\App\Http\Controllers\API\Admin\AdminInvestmentController::class, 'reject']);
    Route::post('/investments/{investment}/complete', [\App\Http\Controllers\API\Admin\AdminInvestmentController::class, 'complete']);

}); // <--- PENUTUP GROUP ADMIN
