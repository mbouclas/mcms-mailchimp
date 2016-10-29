<?php

Route::group(['prefix' => 'admin/api'], function () {
    Route::group(['middleware' =>['level:5']], function($router)
    {

    });

});