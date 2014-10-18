<?php

class UserController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $users = User::with('address')->get();
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
        if ($user = User::with('address')->find($id)) {
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
        $user = User::where('email', '=', $email)->first();
        return $this->makeSuccessResponse("User (Email = $email) fetched.", $user->toArray());
    }

}
