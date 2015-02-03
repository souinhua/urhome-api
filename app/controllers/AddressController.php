<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AddressController
 *
 * @author User
 */
class AddressController extends BaseController {
    
    public function properties() {
        $table = DB::select("select distinct a.city, a.province from property p inner join address a on a.id = p.address_id");
        return $this->makeResponse($table, 200, "Distinct Property Locations fetched.");
    }
    
}
