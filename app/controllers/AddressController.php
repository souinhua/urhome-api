<?php

class AddressController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $addresses = Address::all();
        return $this->makeSuccessResponse("Addresses fetched", $addresses->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $rules = array(
            "address" => "required|max:256", 
            "city" => "required|max:64", 
            "province" => "required|max: 64", 
            "zip" => "required|max:32",
            "lng" => "numeric", 
            "lat" => "numeric",
            "zoom" => "numeric"
        );
        $validation = Validator::make(Input::all(), $rules);
        if($validation->fails()) {
            return $this->makeFailResponse("Address creation could not complete due to validation error(s).", $validation->messages()->getMessages());
        }
        else {
            $address = new Address();
            $address->address = Input::get("address");
            $address->city = Input::get("city");
            $address->province = Input::get("province");
            $address->zip = Input::get("zip");
            $address->lng = Input::get("lng", null);
            $address->lat = Input::get("lat", null);
            $address->zoom = Input::get("zoom", null);
            $address->accessibility = Input::get("accessibility",null);
            $address->save();
            
            return $this->makeSuccessResponse("Address creation successful", $address->toArray());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        if($address = Address::find($id)) {
            return $this->makeSuccessResponse("Address (ID = $id) fetched", $address->toArray());
        }
        else {
            return $this->makeFailResponse("Address does not exist");
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        if($address = Address::find($id)) {
            $address->address = Input::get("address");
            $address->city = Input::get("city");
            $address->province = Input::get("province");
            $address->zip = Input::get("zip");
            $address->lng = Input::get("lng", null);
            $address->lat = Input::get("lat", null);
            $address->zoom = Input::get("zoom", null);
            $address->accessibility = Input::get("accessibility",null);
            $address->save();
            
            return $this->makeSuccessResponse("Address (ID = $id) updated", $address->toArray());
        }
        else {
            return $this->makeFailResponse("Address does not exist");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        if($address = Address::find($id)) {
            $address->delete();
            return $this->makeSuccessResponse("Address (ID = $id) deleted");
        }
        else {
            return $this->makeFailResponse("Address does not exist");
        }
    }

}
