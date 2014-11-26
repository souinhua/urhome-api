<?php
/**
 * Description of PhotoManager
 *
 * @author Janssen Canturias
 */
namespace Helpers\FileManager\Facades;
use Illuminate\Support\Facades\Facade;

class PhotoManager extends Facade{
    
    protected static function getFacadeAccessor() {
        return 'photomanager';
    }
    
}
