<?php

class BaseController extends Controller {

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
        foreach($data as $datum) {
            if($datum->existing) {
                $exisits = true;
                break;
            }
        }
        return $exisits;
    }

}
