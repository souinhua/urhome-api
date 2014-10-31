<?php

/**
 * Description of AclController
 *
 * @author User
 */
class AclController extends BaseController {
    
    /**
     * Fetch all ACLs
     *
     * @return Response
     */
    public function index() {
        $acls = ACL::all();
        return $this->makeSuccessResponse("All ACLs fetched.", $acls->toArray());
    }
    
}
