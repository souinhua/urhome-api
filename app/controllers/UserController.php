<?php

class UserController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        if (Input::has("search")) {
            $search = Input::get('search');
            $users = User::with(array('address', 'acl','photo'))
                    ->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('id', '=', $search)
                    ->get();
        } else {
            $users = User::with(array('address', 'acl','photo'))->get();
        }
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
            $user->created_by = Auth::user()->id;
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
        if ($user = User::with(array('address', 'acl','photo'))->find($id)) {
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

            $user->updated_by = Auth::user()->id;
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
            $user->deleted_by = Auth::user()->id;
            $user->save();
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
        if ($user = User::with(array('address', 'acl'))->where('email', '=', $email)->first()) {
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
                $user->updated_by = Auth::user()->id;
                $user->save();

                return $this->makeSuccessResponse("Address creation/update for User (ID = $id) successful.", $address->toArray());
            } else {
                return $this->makeFailResponse("User (ID = $id) does not exist.");
            }
        }
    }

    /**
     * Check if the email and password matches a user's credentials
     *
     * @param  $email
     * @param  $password
     * @return Response
     */
    public function exists($email, $password) {
        $exist = Auth::validate(array(
                    "email" => $email,
                    "password" => $password
        ));

        if ($exist)
            return $this->getEmail($email);
        else
            return $this->makeFailResponse("User does not exist.");
    }

    /**
     * Get the number of active users
     * 
     * @return Response
     */
    public function count() {
        $count = User::count();
        return $this->makeSuccessResponse("Number of Users fethced.", $count);
    }
    
    /**
     * Store photo for a user
     * 
     * @return Response
     */
    public function postPhoto($id) {
        $rules = array(
            "photo" => "required|image"
        );
        $validation = Validator::make(Input::all(), $rules);
        if($validation->fails()) {
            return $this->makeFailResponse("Photo upload of User (ID = $id) failed due to validation errors.", $validation->messages()->getMessages());
        }
        else {
            if($user = User::find($id)) {
                if(!is_null($user->photo)) {
                    $user->photo->delete();
                }
                
                $extension = Input::file('photo')->getClientOriginalExtension();
                $fileName = Auth::user()->id . '_' . time() . "." . $extension;
                
                $destinationPath = public_path() . "/uploads/users";
                Input::file('photo')->move($destinationPath, $fileName);
                
                $photo = new Photo();
                $photo->path = "$destinationPath/$fileName";
                $photo->url = URL::to("uploads/users/$fileName");
                $photo->uploaded_by = Auth::user()->id;
                $photo->save();
                
                $user->photo_id = $photo->id;
                $user->save();
                
                return $this->makeSuccessResponse("Photo upload of User (ID = $id) was successful", $photo->toArray());
            }
            else {
                return $this->makeFailResponse("User does not exist");
            }
        }
    }

}
