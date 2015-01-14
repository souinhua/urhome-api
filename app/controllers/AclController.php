<?php

/**
 * Description of AclController
 *
 * @author Janssen Canturias
 */
class AclController extends BaseController {
    
    function __construct() {
        parent::__construct();
        
        $this->beforeFilter('auth', array('except'=> ['index','show']));
    }

    /**
     * Fetch all ACLs
     *
     * @return Response
     */
    public function index() {
        $acls = ACL::all();
        return $this->makeResponse($acls, 200, "ACLs Fetched.");
    }
    
    /**
     * Fetch ACL Resource
     *
     * @param id ACL Resource ID
     * @return Response
     */
    public function show($id) {
        if($acl = ACL::find($id)) {
            return $$this->makeResponse($acl, 200, "ACL (ID= $id) resource fethced.");
        }
        else {
            return $this->makeResponse(null, 404, "ACL resource not found.");
        }
    }
    
}
