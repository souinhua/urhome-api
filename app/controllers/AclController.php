<?php

/**
 * Description of AclController
 *
 * @author Janssen Canturias
 */
class AclController extends BaseController {
    
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
            return $this->makeSuccessResponse("ACL (ID = $id) fetched.", $acl->toArray());
        }
        else {
            return $this->makeFailResponse("ACL does not exist.");
        }
    }
    
}
