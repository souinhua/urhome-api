<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the Closure to execute when that URI is requested.
  |
 */

Route::get('/', function() {
    return View::make('home.index');
});

Route::group(array('prefix' => 'v1', "before" => array("json", "oauth")), function() {

    /*
     * Users Resource Routes
     */
    Route::resource('user', 'UserController');
    Route::get('users', 'UserController@index');
    Route::get('user/email/{name}', 'UserController@getEmail');
    Route::post('user/{id}/address', 'UserController@setAddress');
    Route::put('user/{id}/address', 'UserController@setAddress');
});

Route::post('oauth/token', function() {
    $bridgedRequest = OAuth2\HttpFoundationBridge\Request::createFromRequest(Request::instance());
    $bridgedResponse = new OAuth2\HttpFoundationBridge\Response();

    $bridgedResponse = App::make('oauth2')->handleTokenRequest($bridgedRequest, $bridgedResponse);

    return $bridgedResponse;
});
