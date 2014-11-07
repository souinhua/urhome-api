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
Route::get('/test', function() {
    $properties = Property::whereCreated_by(1)->get();
    var_dump($properties);
});

Route::get('/', function() {
    return View::make('home.index');
});

Route::group(array('prefix' => 'v1', "before" => array("json", "oauth")), function() {
    
    Route::get('test', function() {
        $user = Auth::user();
        echo "asd:";
        print_r($user);
    });
    /*
     * ACL Resource Routes
     */
    Route::resource('acl', 'AclController');

    /*
     * Property Resource Routes
     */
    Route::resource('property', 'PropertyController');
    Route::get('properties', 'PropertyController@index');
    Route::get('properties/report', 'PropertyController@report');
    Route::get('property/{id}/photo', 'PropertyController@postPhoto');


    /*
     * Users Resource Routes
     */
    Route::resource('user', 'UserController');
    Route::get('users', 'UserController@index');
    Route::get('user/email/{name}', 'UserController@getEmail');
    Route::post('user/{id}/address', 'UserController@setAddress');
    Route::post('user/{id}/photo', 'UserController@postPhoto');
    Route::put('user/{id}/address', 'UserController@setAddress');
    Route::get('user/exists/{email}/{password}', 'UserController@exists');
    Route::get('users/count', 'UserController@count');

    /*
     * Photos Resource Routes
     */
    Route::resource('photo', 'PhotoController');
    Route::get('photo/{id}/display', 'PhotoController@display');

    /*
     * Types Resource Routes
     */
    Route::resource('type', 'TypeController');
    Route::get('types', 'TypeController@index');

    /*
     * Types Resource Routes
     */
    Route::resource('address', 'AddressController');
    Route::get('addresses', 'AddressController@index');

    /*
     * Amenity Resource Routes
     */
    Route::resource('amenity', 'AmenityController');
    Route::get('amenities', 'AmenityController@index');
    Route::post('amenity/{id}/photo', 'AmenityController@savePhoto');
    
    /*
     * Feature Resource Routes
     */
    Route::resource('feature', 'FeatureController');
    Route::get('features', 'FeatureController@index');
});

Route::post('oauth/token', function() {
    $bridgedRequest = OAuth2\HttpFoundationBridge\Request::createFromRequest(Request::instance());
    $bridgedResponse = new OAuth2\HttpFoundationBridge\Response();

    $bridgedResponse = App::make('oauth2')->handleTokenRequest($bridgedRequest, $bridgedResponse);

    return $bridgedResponse;
});
