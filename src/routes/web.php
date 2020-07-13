<?php

Route::group(array('namespace' => 'Codificar\Withdrawals\Http\Controllers'), function () {

    Route::group(['prefix' => 'libs/withdrawals', 'middleware' => 'auth.provider_api:api'], function () {

        Route::post('/report', 'WithdrawalsController@getWithdrawalsReport');
    
        Route::post('/add', 'WithdrawalsController@addWithDraw');
    });

});
