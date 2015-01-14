<?php

class BaseController extends Controller {

    function __construct() {
        $this->initCustomValidations();
    }

    /**
     * @param string $entity
     * @param int $identifier
     * @param string $field
     * @return boolean
     */
    protected function entityExists($entity, $identifier, $field = 'id') {
        $data = DB::select("SELECT EXISTS(SELECT $field FROM $entity WHERE $field = ?) AS existing", array($identifier));
        $exisits = false;
        foreach ($data as $datum) {
            if ($datum->existing) {
                $exisits = true;
                break;
            }
        }
        return $exisits;
    }

    /**
     * Initialize custome validations
     */
    protected function initCustomValidations() {
        Validator::extend('cloudinary_photo', function($attribute, $value, $parameters) {
            if (isset($value['public_id'])) {
                try {
                    $cloudy = new \Cloudinary\Api();
                    $cloudy->resource($value['public_id']);
                    return true;
                } catch (Exception $e) {
                    return false;
                }
            } else {
                return false;
            }
        });
    }

    /**
     * Check if a key exists in $_POST or $_GET
     * @param any $key
     * @return boolean
     */
    protected function hasInput($key) {
        $inputs = Input::all();
        return array_key_exists($key, $inputs);
    }
    
    /**
     * creates an HTTP JSON Response
     * 
     * @param string|object|null $data
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return HTTP Response
     */

    protected function makeResponse($data, $code, $message, $headers = array()) {
        $responseHeaders = array_merge($headers, array(
            "X-Urhome-Message" => $message,
            "X-Urhome" => "Sure"
        ));
        return Response::json($data, $code, $responseHeaders);
    }
}
