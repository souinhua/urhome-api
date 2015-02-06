<?php

/**
 * Description of AddressController
 *
 * @author User
 */
class AddressController extends BaseController {

    public function properties() {
        $table = DB::select("select distinct a.city, a.province, lower(replace(concat(a.city,' ',a.province),' ','-')) slug from address a inner join property p on p.address_id = a.id");
        return $this->makeResponse($table, 200, "Distinct Property Locations fetched.");
    }

}
