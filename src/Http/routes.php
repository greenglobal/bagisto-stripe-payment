<?php

Route::group(['middleware' => ['web', 'locale', 'theme', 'currency']], function () {
    Route::prefix('user')->group(function () {
        Route::post('/card-for-charge', 'GGPHP\Payment\Http\Controllers\PaymentController@addCardForCharge')
        ->name('user.card.store-for-charge');
    });
});
