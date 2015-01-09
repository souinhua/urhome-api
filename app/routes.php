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
    $properties = Property::all();
    $count = Property::count();
    
    return Response::json($properties, 200, array("X-Urhome-Count" => $count));
});

Route::get("docs", function() {
    return View::make("home.index");
});

Route::group(array('prefix' => 'v1', "before" => array("json", "oauth")), function() {
    /*
     * ACL Resource Routes
     */
    Route::resource('acl', 'AclController');

    /*
     * Property Resource Routes
     */
    Route::get('properties/report', 'PropertyController@report');
    Route::post('properties/{id}/main-photo', 'PropertyController@mainPhoto');
    Route::post('properties/{id}/publish', 'PropertyController@publish');
    Route::resource('properties', 'PropertyController');

    Route::resource('properties.feature', 'PropertyFeatureController');
    Route::resource('properties.details', 'PropertyDetailsController');
    Route::resource('properties.specs', 'PropertySpecController');

    Route::get("properties/{propertyId}/photos/count", 'PropertyPhotoController@count');
    Route::resource('properties.photos', 'PropertyPhotoController');
    Route::resource('properties.amenities', 'PropertyAmenityController');

    Route::post("properties/{propertyId}/units/{unitId}/main-photo", 'PropertyUnitController@mainPhoto');
    Route::post("properties/{properytId}/units/{unitId}/details", 'PropertyUnitController@details');
    Route::put("properties/{properytId}/units/{unitId}/details", 'PropertyUnitController@details');
    Route::resource('properties.units', 'PropertyUnitController');
    
    /*
     * Users Resource Routes
     */
    Route::get('users/email/{name}', 'UserController@getEmail');
    Route::get('users/exists/{email}/{password}', 'UserController@exists');
    Route::post('users/{id}/photo', 'UserController@photo');
    Route::resource('users', 'UserController');

    /*
     * Photos Resource Routes
     */
    Route::get('photos/{id}/display', 'PhotoController@display');
    Route::resource('photos', 'PhotoController');

    /*
     * Types Resource Routes
     */
    Route::resource('types', 'TypeController');

    /*
     * Types Resource Routes
     */
    Route::resource('addresses', 'AddressController');

    /*
     * Amenity Resource Routes
     */
    Route::resource('amenities', 'AmenityController');
    Route::post('amenities/{id}/photo', 'AmenityController@savePhoto');

    /*
     * Feature Resource Routes
     */
    Route::resource('features', 'FeatureController');

    /**
     * Specifications
     */
    Route::resource('specs', 'SpecController');
    
    /**
     * Unit Features
     */
    Route::resource('units.features', 'UnitFeatureController');
    Route::resource('units.specs', 'UnitSpecController');
    
    /*
     * Developer Resource
     */
    Route::resource('developers', 'DeveloperController');
    
});

Route::post('oauth/token', function() {
    $bridgedRequest = OAuth2\HttpFoundationBridge\Request::createFromRequest(Request::instance());
    $bridgedResponse = new OAuth2\HttpFoundationBridge\Response();

    $bridgedResponse = App::make('oauth2')->handleTokenRequest($bridgedRequest, $bridgedResponse);

    return $bridgedResponse;
});
