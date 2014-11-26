<?php

/**
 * Description of PhotoManagerServiceProvider
 *
 * @author Janssen Canturias
 */
namespace Helpers\FileManager;
use Illuminate\Support\ServiceProvider;


class PhotoManagerServiceProvider extends ServiceProvider{
    
    public function register() {
        $this->app['photomanager'] = $this->app->share(function($app) {
            return new PhotoManager();
        });
    }
    
}
