<?php

Route::group(array('namespace' => 'Codificar\Withdrawals\Http\Controllers'), function () {

    Route::group(['prefix' => 'libs', 'middleware' => 'auth.provider_api:api'], function () {

        Route::post('/get_withdrawals_report', 'WithdrawalsController@getWithdrawalsReport');
    
    });

});
