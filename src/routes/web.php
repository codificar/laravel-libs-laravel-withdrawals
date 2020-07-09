
<?php
Route::group(['namespace' => 'Codificar\Withdrawals\Http\Controllers', 'middleware' => ['web']], function(){
    Route::get('contact', 'WithdrawalsController@index');
});
