
<?php
Route::group(['namespace' => 'Codificar\Generic\Http\Controllers', 'middleware' => ['web']], function(){
    Route::get('contact', 'GenericController@index');
    Route::post('contact', 'GenericController@sendMail')->name('contact');
});
