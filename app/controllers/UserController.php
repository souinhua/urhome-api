<?php

class UserController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $users = User::with(array('address','acl'))->get();
        return $this->makeSuccessResponse("All users fetched", $users->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $rules = array(
            "name" => "required|max:128",
            "email" => "required|max:128|email",
            "password" => "required|min:6",
            "acl_id" => "required|exists:acl,id",
        );
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeFailResponse("User creation could not complete due to validation error(s).", $validation->messages()->getMessages());
        } else {
            $user = new User();
            $user->name = Input::get('name');
            $user->email = Input::get('email');
            $user->phone = Input::get('phone', null);
            $user->acl_id = Input::get('acl_id');

            $user->password = Hash::make(Input::get("password"));

            $user->save();

            return $this->makeSuccessResponse("User creation successful.", $user->toArray());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        if ($user = User::with(array('address','acl'))->find($id)) {
            return $this->makeSuccessResponse("User (ID = $user->id) fetched", $user->toArray());
        } else {
            return $this->makeFailResponse("User (ID = $id) does not exist.");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        if ($user = User::find($id)) {
            $user->name = Input::get('name', $user->name);
            $user->email = Input::get('email', $user->email);
            $user->phone = Input::get('phone', $user->phone);
            $user->acl_id = Input::get('acl_id', $user->acl_id);

            if (Input::has("password"))
                $user->password = Hash::make(Input::get("password"));

            $user->save();
            return $this->makeSuccessResponse("User (ID = $id) updated", $user->toArray());
        }
        else {
            return $this->makeFailResponse("User (ID = $id) does not exist.");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        if ($user = User::find($id)) {
            $user->delete();
            return $this->makeSuccessResponse("User (ID = $user->id) deleted", $user->toArray());
        } else {
            return $this->makeFailResponse("User (ID = $id) does not exist.");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $email
     * @return Response
     */
    public function getEmail($email) {
        if ($user = User::where('email', '=', $email)->first()) {
            return $this->makeSuccessResponse("User (Email = $email) fetched.", $user->toArray());
        } else {
            return $this->makeFailResponse("User (Email = $email) does not exist.");
        }
    }

    /**
     * Store the new address resource to a user.
     *
     * @param  int  $id
     * @return Response
     */
    public function setAddress($id) {
        $rules = array(
            "address" => "required|max:256",
            "city" => "required|max:64",
            "province" => "required|max:64",
            "zip" => "required|max:32",
            "lng" => "numeric",
            "lat" => "numeric",
            "zoom" => "numeric",
        );
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeFailResponse("Address creation of User (ID = $id) failed due to validation errors.", $validation->messages()->getMessages());
        } else {
            if ($user = User::find($id)) {
                if (is_null($user->address)) {
                    $address = new Address();
                } else {
                    $address = $user->address;
                }

                $address->address = Input::get('address');
                $address->city = Input::get('city');
                $address->province = Input::get('province');
                $address->zip = Input::get('zip');

                $address->lng = Input::get('lng', null);
                $address->lat = Input::get('lat', null);
                $address->zoom = Input::get('zoom', null);

                $address->accessibility = Input::get('accessibility', null);
                $address->save();

                $user->address_id = $address->id; // Attach the Address ID to the User
                $user->save();
                
                return $this->makeSuccessResponse("Address creation/update for User (ID = $id) successful.", $address->toArray());
            } else {
                return $this->makeFailResponse("User (ID = $id) does not exist.");
            }
        }
    }

}
