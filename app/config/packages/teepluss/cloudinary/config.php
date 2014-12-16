<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Cloudinary API configuration
    |--------------------------------------------------------------------------
    |
    | Before using Cloudinary you need to register and get some detail
    | to fill in below, please visit cloudinary.com.
    |
    */

    'cloudName'  => 'urhome-ph',
    'baseUrl'    => 'http://res.cloudinary.com/urhome-ph',
    'secureUrl'  => 'https://res.cloudinary.com/urhome-ph',
    'apiBaseUrl' => 'https://api.cloudinary.com/v1_1/urhome-ph',
    'apiKey'     => '685992343944418',
    'apiSecret'  => 'Fybpw94RH24gBlgQkRngXgs7Smg',

    /*
    |--------------------------------------------------------------------------
    | Default image scaling to show.
    |--------------------------------------------------------------------------
    |
    | If you not pass options parameter to Cloudy::show the default
    | will be replaced.
    |
    */

    'scaling'    => array(
        'format' => 'png',
        'width'  => 150,
        'height' => 150,
        'crop'   => 'fit',
        'effect' => null
    )

);
