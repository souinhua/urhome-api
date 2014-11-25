<?php

/*
  |--------------------------------------------------------------------------
  | Application & Route Filters
  |--------------------------------------------------------------------------
  |
  | Below you will find the "before" and "after" events for the application
  | which may be used to do any work before or after a request into your
  | application. Here you may also register your custom route filters.
  |
 */

App::before(function($request) {
    //
});


App::after(function($request, $response) {
    Auth::logout();
});

App::singleton('oauth2', function() {

//    $storage = new OAuth2\Storage\Pdo(array('dsn' => 'mysql:dbname=urhome_api_db;host=localhost', 'username' => 'root', 'password' => ''));
    $storage = new OAuth2\Storage\Pdo(array('dsn' => 'mysql:dbname=urhome-api;host=urhome-api.mysql.eu1.frbit.com', 'username' => 'urhome-api', 'password' => 'LS9UNMrNeFymGJqU'));
    $server = new OAuth2\Server($storage);

    $server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));

    $userStorage = new UserStorage();
    $server->addGrantType(new OAuth2\GrantType\UserCredentials($userStorage));

    return $server;
});

/*
  |--------------------------------------------------------------------------
  | Authentication Filters
  |--------------------------------------------------------------------------
  |
  | The following filters are used to verify that the user of the current
  | session is logged into this application. The "basic" filter easily
  | integrates HTTP Basic authentication for quick, simple checking.
  |
 */

Route::filter('auth', function() {
    if (Auth::guest()) {
        if (Request::ajax()) {
            return Response::make('Unauthorized', 401);
        } else {
            return Redirect::guest('login');
        }
    }
});


Route::filter('auth.basic', function() {
    return Auth::basic();
});

/*
  |--------------------------------------------------------------------------
  | Guest Filter
  |--------------------------------------------------------------------------
  |
  | The "guest" filter is the counterpart of the authentication filters as
  | it simply checks that the current user is not logged in. A redirect
  | response will be issued if they are, which you may freely change.
  |
 */

Route::filter('guest', function() {
    if (Auth::check())
        return Redirect::to('/');
});

/*
  |--------------------------------------------------------------------------
  | CSRF Protection Filter
  |--------------------------------------------------------------------------
  |
  | The CSRF filter is responsible for protecting your application against
  | cross-site request forgery attacks. If this special token in a user
  | session does not match the one given in this request, we'll bail.
  |
 */

Route::filter('csrf', function() {
    if (Session::token() != Input::get('_token')) {
        throw new Illuminate\Session\TokenMismatchException;
    }
});




//Route::filter('json', function() {
//    if (Request::isMethod('post') || Request::isMethod('put')) {
//        if (Request::isJson()) {
//            $requestData = Request::instance()->getContent();
//            json_decode($requestData);
//        }
//    }
//});


Route::filter('oauth', function() {
    $bridgedRequest = OAuth2\HttpFoundationBridge\Request::createFromRequest(Request::instance());
    print_r($bridgedRequest);
    exit;
    $bridgedResponse = new OAuth2\HttpFoundationBridge\Response();

    if (App::make('oauth2')->verifyResourceRequest($bridgedRequest, $bridgedResponse)) {

        $token = App::make('oauth2')->getAccessTokenData($bridgedRequest);
        if (isset($token['user_id'])) {
            if ($user = User::find($token['user_id'])) {
                Auth::login($user);
            } else {
                return Response::json(array(
                            'status' => 'UNAUTHORIZED'
                                ), 401);
            }
        }
    } else {
        return Response::json(array(
                    'status' => 'UNAUTHORIZED'
                        ), $bridgedResponse->getStatusCode());
    }
});
