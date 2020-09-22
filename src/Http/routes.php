<?php

Route::group(['middleware' => ['web', 'locale', 'theme', 'currency']], function () {
    Route::prefix('customer')->group(function () {
        Route::group(['middleware' => ['customer']], function () {
            Route::get('/{id}/card', 'GGPHP\Payment\Http\Controllers\PaymentController@getCard')->name('customer.card.view');
            Route::post('/{id}/card-for-charge', 'GGPHP\Payment\Http\Controllers\PaymentController@addCardForCharge')
            ->name('customer.card.store-for-charge');
        });
    });
});
