<?php

// Routes of web
Route::group(array('namespace' => 'Codificar\Withdrawals\Http\Controllers'), function () {
    

    Route::group(['prefix' => 'admin/libs', 'middleware' => 'auth.admin_api'], function () {

        Route::get('/cnab_settings', array('as' => 'webAdminWithdrawalsSettings', 'uses' => 'WithdrawalsController@getCnabSettings'));
    
    });
   
});

// Routes of apps
Route::group(array('namespace' => 'Codificar\Withdrawals\Http\Controllers'), function () {

    Route::group(['prefix' => 'libs/withdrawals', 'middleware' => 'auth.provider_api:api'], function () {

        Route::post('/report', 'WithdrawalsController@getWithdrawalsReport');
    
        Route::post('/add', 'WithdrawalsController@addWithDraw');
    });

});
