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

/*
|--------------------------------------------------------------------------
| Public Authentication
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Public Market
|--------------------------------------------------------------------------
*/

Route::prefix('markets')->group(function () {

    Route::get(
        '/',
        [MarketController::class, 'index']
    );

    Route::get(
        '/ticker',
        [MarketController::class, 'ticker']
    );

    Route::get(
        '/{symbol}/orderbook',
        [MarketController::class, 'orderBook']
    );

    Route::get(
        '/{symbol}/candles',
        [MarketController::class, 'candles']
    );

    Route::get(
        '/{symbol}',
        [MarketController::class, 'show']
    );

});

/*
|--------------------------------------------------------------------------
| Public Data
|--------------------------------------------------------------------------
*/

Route::get(
    '/wallets',
    [WalletController::class, 'index']
);

Route::get(
    '/payment-methods',
    [UserPaymentMethodController::class, 'index']
);

Route::middleware('auth:sanctum')->get('/exchange-rates', [ExchangeRateController::class, 'index']);


Route::get(
    '/payment-accounts',
    [UserPaymentAccountController::class, 'index']
);

Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'profile']);
/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::apiResource(
    'admin/payment-methods',
    AdminPaymentMethodController::class
);

Route::apiResource(
    'admin/payment-accounts',
    AdminPaymentAccountController::class
);

Route::get(
    '/admin/deposits',
    [AdminDepositController::class, 'index']
);

Route::get(
    '/admin/deposits/{deposit}',
    [AdminDepositController::class, 'show']
);

Route::post(
    '/admin/deposits/{deposit}/approve',
    [AdminDepositController::class, 'approve']
);

Route::post(
    '/admin/deposits/{deposit}/reject',
    [AdminDepositController::class, 'reject']
);

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get(
        '/profile',
        [AuthController::class, 'profile']
    );

    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    );

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    );

    Route::get(
        '/wallets',
        [WalletController::class, 'index']
    );

    Route::get(
        '/payment-methods',
        [UserPaymentMethodController::class, 'index']
    );

    Route::get(
        '/payment-accounts',
        [UserPaymentAccountController::class, 'index']
    );

    Route::get(
        '/deposits',
        [DepositController::class, 'index']
    );

    Route::post(
        '/deposits',
        [DepositController::class, 'store']
    );

    Route::get(
        '/deposits/{deposit}',
        [DepositController::class, 'show']
    );

    Route::post(
        '/orders',
        [OrderController::class, 'store']
    );
        /*
|--------------------------------------------------------------------------
| Investment
|--------------------------------------------------------------------------
*/

    Route::get(

        '/investment-products',

        [\App\Http\Controllers\API\InvestmentController::class,'products']

    );

    Route::get(

        '/investment-products/{investmentProduct}',

        [\App\Http\Controllers\API\InvestmentController::class,'showProduct']

    );

    Route::get(

        '/investments',

        [\App\Http\Controllers\API\InvestmentController::class,'index']

    );

    Route::post(

        '/investments',

        [\App\Http\Controllers\API\InvestmentController::class,'store']

    );

    Route::get(

        '/investments/{investmentOrder}',

        [\App\Http\Controllers\API\InvestmentController::class,'show']

    );

    /*
|--------------------------------------------------------------------------
| Investment Products
|--------------------------------------------------------------------------
*/

Route::get(

    '/investment-products',

    [\App\Http\Controllers\API\InvestmentController::class,'index']

);

Route::get(

    '/investment-products/{product}',

    [\App\Http\Controllers\API\InvestmentController::class,'show']

);

/*
|--------------------------------------------------------------------------
| User Investments
|--------------------------------------------------------------------------
*/

Route::get(

    '/investments',

    [\App\Http\Controllers\API\InvestmentController::class,'myInvestments']

);

Route::get(

    '/investments/active',

    [\App\Http\Controllers\API\InvestmentController::class,'active']

);

Route::get(

    '/investments/pending',

    [\App\Http\Controllers\API\InvestmentController::class,'pending']

);

Route::get(

    '/investments/completed',

    [\App\Http\Controllers\API\InvestmentController::class,'completed']

);

Route::get(

    '/investments/{investment}',

    [\App\Http\Controllers\API\InvestmentController::class,'detail']

);

Route::post(

    '/investment-products/{product}/invest',

    [\App\Http\Controllers\API\InvestmentController::class,'invest']

);

Route::post(

    '/investments/{investment}/cancel',

    [\App\Http\Controllers\API\InvestmentController::class,'cancel']

);

/*
|--------------------------------------------------------------------------
| Admin Investment
|--------------------------------------------------------------------------
*/

Route::prefix('admin')

->middleware([

    'auth:sanctum',

    'admin'

])

->group(function(){

    /*
    |--------------------------------------------------------------------------
    | Category
    |--------------------------------------------------------------------------
    */

    Route::apiResource(

        'investment-categories',

        \App\Http\Controllers\API\Admin\InvestmentCategoryController::class

    );

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    Route::apiResource(

        'investment-products',

        \App\Http\Controllers\API\Admin\InvestmentProductController::class

    );

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(

        '/investments/dashboard',

        [

            \App\Http\Controllers\API\Admin\AdminInvestmentController::class,

            'dashboard'

        ]

    );

    /*
    |--------------------------------------------------------------------------
    | List
    |--------------------------------------------------------------------------
    */

    Route::get(

        '/investments',

        [

            \App\Http\Controllers\API\Admin\AdminInvestmentController::class,

            'index'

        ]

    );

    Route::get(

        '/investments/pending',

        [

            \App\Http\Controllers\API\Admin\AdminInvestmentController::class,

            'pending'

        ]

    );

    Route::get(

        '/investments/active',

        [

            \App\Http\Controllers\API\Admin\AdminInvestmentController::class,

            'active'

        ]

    );

    Route::get(

        '/investments/completed',

        [

            \App\Http\Controllers\API\Admin\AdminInvestmentController::class,

            'completed'

        ]

    );

    Route::get(

        '/investments/rejected',

        [

            \App\Http\Controllers\API\Admin\AdminInvestmentController::class,

            'rejected'

        ]

    );

    Route::get(

        '/investments/cancelled',

        [

            \App\Http\Controllers\API\Admin\AdminInvestmentController::class,

            'cancelled'

        ]

    );

    /*
    |--------------------------------------------------------------------------
    | Detail
    |--------------------------------------------------------------------------
    */

    Route::get(

        '/investments/{investment}',

        [

            \App\Http\Controllers\API\Admin\AdminInvestmentController::class,

            'show'

        ]

    );

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    Route::post(

        '/investments/{investment}/approve',

        [

            \App\Http\Controllers\API\Admin\AdminInvestmentController::class,

            'approve'

        ]

    );

    Route::post(

        '/investments/{investment}/reject',

        [

            \App\Http\Controllers\API\Admin\AdminInvestmentController::class,

            'reject'

        ]

    );

    Route::post(

        '/investments/{investment}/complete',

        [

            \App\Http\Controllers\API\Admin\AdminInvestmentController::class,

            'complete'

        ]

    );

    // Route sementara untuk Dashboard Stats
// Nanti bisa diganti dengan Controller yang proper setelah fitur trading dibuat

Route::middleware('auth:sanctum')->group(function () {
    
    // ... route orders/positions/trades yang sudah ada (POST) biarkan saja ...

    // TAMBAHKAN ROUTE GET INI DI BAWAHNYA:
    Route::get('/orders', function () {
        return response()->json([]); // Return array kosong dulu
    });

    Route::get('/positions', function () {
        return response()->json([]);
    });

    Route::get('/trades', function () {
        return response()->json([]);
    });

    // ... route POST yang sudah ada biarkan saja ...

// TAMBAHKAN ROUTE GET INI DI BAGIAN BAWAH:
Route::middleware('auth:sanctum')->get('/orders', function () {
    return response()->json([]); // Return array kosong dulu sebagai placeholder
});

Route::middleware('auth:sanctum')->get('/positions', function () {
    return response()->json([]);
});

Route::middleware('auth:sanctum')->get('/trades', function () {
    return response()->json([]);
});

    // Pastikan route exchange-rates juga ada (dari troubleshooting sebelumnya)
    Route::get('/exchange-rates', [App\Http\Controllers\API\ExchangeRateController::class, 'index']);
});

});

});
