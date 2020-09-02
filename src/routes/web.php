<?php


// Rotas das views do laravel/vue
Route::group(array('namespace' => 'Codificar\Withdrawals\Http\Controllers'), function () {
    
    // Rotas de saques do admin
    Route::group(['prefix' => 'admin/libs', 'middleware' => 'auth.admin'], function () {

        //Configuracoes cnab
        Route::group(['prefix' => '/cnab_settings'], function () {
            Route::get('/', array('as' => 'webAdminCnabSettings', 'uses' => 'WithdrawalsController@getCnabSettings'));
            Route::post('/save', 'WithdrawalsController@saveCnabSettings');
            Route::post('/create_cnab_file', 'WithdrawalsController@createCnabFile');
            Route::post('/send_ret_file', 'WithdrawalsController@sendRetFile');
            Route::post('/delete_cnab_file', 'WithdrawalsController@deleteCnabFile');
        });

        //Relatorio de saques
        Route::group(['prefix' => '/withdrawals'], function () {
            Route::get('/', array('as' => 'webAdminWithdrawalsReport', 'uses' => 'WithdrawalsController@getWithdrawalsReportWeb'));
            Route::post('/confirm_withdraw', array('as' => 'webAdminConfirmWithdraw', 'uses' => 'WithdrawalsController@confirmWithdraw'));
        });

        //Configuracoes de saque
        Route::group(['prefix' => '/withdrawals_settings'], function () {
            Route::get('/', array('as' => 'webAdminWithdrawalsSettings', 'uses' => 'WithdrawalsController@getWithdrawalsSettingsWeb'));
            Route::post('/save', array('as' => 'webAdminSaveWithdrawSettings', 'uses' => 'WithdrawalsController@saveWithdrawalsSettings'));
        });
    });

    // Rota para relatorio de saques do provider
    Route::group(['prefix' => '/provider/libs', 'middleware' => 'auth.provider'], function () {
        Route::get('/withdrawals', array('as' => 'webProviderWithdrawalsReport', 'uses' => 'WithdrawalsController@getWithdrawalsReportWeb'));
        Route::post('/withdrawals/WithdrawAdd', array('as' => 'providerWithdrawAdd', 'uses' => 'WithdrawalsController@addWithDraw'));
        Route::post('/withdrawals/create-user-bank-account', array('as' => 'providerAddBankAccount', 'uses' => 'WithdrawalsController@createUserBankAccount'));
    });

});



// Rotas do app provider
Route::group(array('namespace' => 'Codificar\Withdrawals\Http\Controllers'), function () {

    Route::group(['prefix' => 'libs/withdrawals', 'middleware' => 'auth.provider_api:api'], function () {

        Route::post('/report', 'WithdrawalsController@getWithdrawalsReport');
    
        Route::post('/add', 'WithdrawalsController@addWithDraw');

        Route::post('/settings', 'WithdrawalsController@getWithdrawSettings');
    });

});

/**
 * Rota para permitir utilizar arquivos de traducao do laravel (dessa lib) no vue js
 */
Route::get('/libs/lang.trans/{file}', function () {
    $fileNames = explode(',', Request::segment(3));
    $lang = config('app.locale');
    $files = array();
    foreach ($fileNames as $fileName) {
        array_push($files, __DIR__.'/../resources/lang/' . $lang . '/' . $fileName . '.php');
    }
    $strings = [];
    foreach ($files as $file) {
        $name = basename($file, '.php');
        $strings[$name] = require $file;
    }

    header('Content-Type: text/javascript');
    return ('window.lang = ' . json_encode($strings) . ';');
    exit();
})->name('assets.lang');