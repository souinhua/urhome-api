<?php

class BaseController extends Controller {

    function __construct() {
        $this->initCustomValidations();
    }

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout() {
        if (!is_null($this->layout)) {
            $this->layout = View::make($this->layout);
        }
    }

    /**
     * Makes JSON successful response
     * 
     * @param array $data Returned array of requested data
     * @return Response JSON success response
     */
    protected function makeSuccessResponse($message, $data = null) {
        $return = array(
            'status' => 'OK',
            'message' => $message
        );

        if (isset($data))
            $return['data'] = $data;

        return Response::json($return, 200);
    }

    /**
     * Makes JSON failure response
     * 
     * @param string $message Error message
     * @param array $validations OPTIONAL if there are validations to report
     * @return Response JSON failure response
     */
    protected function makeFailResponse($message, array $validations = null, $errorCode = 400) {
        $return = array(
            'status' => 'FAILED',
            'message' => $message
        );

        if (isset($validations))
            $return['error'] = $validations;

        return Response::json($return, $errorCode);
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
    private function initCustomValidations() {
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
     * @param type $data
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return HTTP Response
     */

    protected function makeResponse($data, $code, $message, $headers = array()) {
        $responseHeaders = array_merge($headers, array(
            "X-Urhome-Message" => $message,
        ));
        return Response::json($data, $code, $responseHeaders);
    }
}
