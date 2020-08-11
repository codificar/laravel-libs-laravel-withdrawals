<?php


// Rotas das views do laravel/vue
Route::group(array('namespace' => 'Codificar\Withdrawals\Http\Controllers'), function () {
    
    // (View) Rotas de configuracoes do saque (cnab)
    Route::group(['prefix' => 'admin/libs', 'middleware' => 'auth.admin_api'], function () {
        Route::get('/cnab_settings', array('as' => 'webAdminWithdrawalsSettings', 'uses' => 'WithdrawalsController@getCnabSettings'));
        Route::post('/cnab_settings/save', 'WithdrawalsController@saveCnabSettings');
    });

    // (View) Rota para relatorio de saques do admin
    Route::group(['prefix' => '/admin/libs', 'middleware' => 'auth.admin_api'], function () {
        Route::get('/withdrawals', array('as' => 'webAdminWithdrawalsReport', 'uses' => 'WithdrawalsController@getWithdrawalsReportWeb'));
    });

    // (View) Rota para relatorio de saques do provider
    Route::group(['prefix' => '/provider/libs', 'middleware' => 'auth.provider'], function () {
        Route::get('/withdrawals', array('as' => 'webProviderWithdrawalsReport', 'uses' => 'WithdrawalsController@getWithdrawalsReportWeb'));
    });

    // (View) Rota para relatorio de saques do user
    Route::group(['prefix' => '/user/libs', 'middleware' => 'auth.user'], function () {
        Route::get('/withdrawals', array('as' => 'webUserWithdrawalsReport', 'uses' => 'WithdrawalsController@getWithdrawalsReportWeb'));
    });

    
    // (Api) Rotas da api dar baixa no saque manualmente, com envio de comprovante (foto) e data
    Route::group(['prefix' => '/admin/libs', 'middleware' => 'auth.admin_api'], function () {
        Route::post('/withdrawals/confirm_withdraw', array('as' => 'webAdminConfirmWithdraw', 'uses' => 'WithdrawalsController@confirmWithdraw'));
    });

    // (Api) Rotas da api para dicionar um saque
    Route::group(['prefix' => '/provider/libs', 'middleware' => 'auth.provider'], function () {
        Route::post('/withdrawals/WithdrawAdd', array('as' => 'providerWithdrawAdd', 'uses' => 'WithdrawalsController@addWithDraw'));
    });

    // (Api) Rota para um prestador adicionar um banco
    Route::group(['prefix' => '/provider/libs', 'middleware' => 'auth.provider'], function () {
        Route::post('/withdrawals/create-user-bank-account', array('as' => 'providerAddBankAccount', 'uses' => 'WithdrawalsController@createUserBankAccount'));
    });
   
});



// Rotas dos apps
Route::group(array('namespace' => 'Codificar\Withdrawals\Http\Controllers'), function () {

    Route::group(['prefix' => 'libs/withdrawals', 'middleware' => 'auth.provider_api:api'], function () {

        Route::post('/report', 'WithdrawalsController@getWithdrawalsReport');
    
        Route::post('/add', 'WithdrawalsController@addWithDraw');
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