<?php

class UserController extends \BaseController {

    
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $query = User::with(array('address', 'acl', 'photo'));
        if (Input::has("search")) {
            $search = Input::get('search');
            $query = $query
                    ->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('id', '=', $search);
        }

        if (Input::has('acl')) {
            $acl = Input::get('acl');
            if (is_array($acl)) {
                $query = $query->whereIn('acl_id', $acl);
            } else {
                $query = $query->where('acl_id', '=', $acl);
            }
        }

        $limit = Input::get("limit", 1000);
        $offset = Input::get("offset", 0);

        $count = $query->count();
        $users = $query->take($limit)->skip($offset)->get();

        return $this->makeResponse($users, 200, "User resources fetched.", array("X-Total-Count" => $count));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $rules = array(
            "name" => "required|max:128",
            "email" => "required|max:128|email|unique:user,email",
            "password" => "required|min:6",
            "conf_password" => "required|min:6|same:password",
            "acl_id" => "required|exists:acl,id",
            "account_type" => "required|in:urhome,facebook,google"
        );
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeResponse($validation->messages(), 400, "Request failed in User Resource validation");
        } else {
            $user = new User();
            $user->name = Input::get('name');
            $user->email = Input::get('email');
            $user->phone = Input::get('phone', null);
            $user->acl_id = Input::get('acl_id');

            $user->password = Hash::make(Input::get("password"));
            $user->created_by_id = Auth::id();
            
            $user->account_type = Input::get("account_type");
            $user->save();

            return $this->makeResponse($user, 201, "User resource created.");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        
        $user = Cache::remember("user-$id", 1440, function() use($id) {
            return User::with(array('address', 'acl', 'photo'))->find($id);
        });
        
        if ($user) {
            return $this->makeResponse($user ,200,"User (ID = $user->id) resource fetched.");
        } else {
            return $this->makeResponse(null, 404, "User resource not found.");
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
            $rules = array(
                "name" => "max:64",
                "title" => "max:32",
                "email" => "email|unique:user,email,$id",
                "phone" => "max:32",
                "acl_id" => "exists:acl,id",
                "password" => "min:6",
            );
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeResponse($validation->messsages(), 400, "Request failed in User resource validation.");
            } else {
                unset($rules["password"]); // exclude password field for the loop
                foreach ($rules as $fieldName => $rule) {
                    if ($this->hasInput($fieldName)) {
                        $user->$fieldName = Input::get($fieldName);
                    }
                }

                if (Input::has("password")) {
                    $user->password = Hash::make(Input::get("password"));
                }

                $user->updated_by_id = Auth::user()->id;
                $user->save();
                
                Cache::forget("user-$id");
                
                return $this->makeResponse($user, 200, "User (ID = $id) updated.");
            }
        } else {
            return $this->makeResponse(null, 404, "User resource not found.");
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
            return $this->makeResponse($user, 204, "User (ID = $user->id) deleted.");
        } else {
            return $this->makeResponse(null, 404, "User resource not found.");
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
            return $this->makeResponse($user, 200, "User (Email = $email) fetched.");
        } else {
            return $this->makeResponse(null, 404, "User (Email = $email) does not exist.");
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
            return $this->makeResponse(null, 404, "User resource not found.");
    }

    /**
     * Store the Photo resource to a user.
     *
     * @param  int  $id
     * @return Response
     */
    public function photo($id) {
        if ($user = User::find($id)) {
            $rules = array(
                "photo" => "required|cloudinary_photo",
                "caption" => "max:256"
            );
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeResponse($validation->messages(), 400, "Invalid cloudinary resource public ID");
            }
            else {
                $data = Input::get('photo');
                $photo = PhotoManager::createCloudinary($data['public_id'], $user, Input::get('caption'), $data);

                if (!is_null($user->photo)) {
                    $user->photo->delete();
                }

                $user->photo_id = $photo->id;
                $user->save();
                
                return $this->makeResponse($photo, 201, "Cloudinary resource linked to User (ID = $id).");
            }
        } else {
            return $this->makeResponse(null, 404, "User resource not found.");
        }
    }
    
    /**
     * Updates the User Address resource
     * 
     * @param int $id
     * @return Response
     */
    public function address($id) {
        $input = Input::all();
        $input['id'] = $id;
        $rules = array(
            "address" => "required|max:256", 
            "city" => "required|max:64", 
            "province" => "required|max:64", 
            "zip" => "required|numeric", 
            "lng" => "numeric", 
            "lat" => "numeric", 
            "zoom" => "numeric", 
            "id" => "required|numeric|exists:user,id"
        );
        
        $validation = Validator::make($input, $rules);
        if($validation->fails()) {
            return $this->makeResponse($validation->messages(), 400, "Request failed in User Address validation.");
        }
        else {
            $user = User::find($id);
            $address = $user->address;
            if(is_null($address)) {
                $address = new Addess();
            }
            $address->address = Input::get("address");
            $address->city = Input::get("city");
            $address->province = Input::get("province");
            $address->zip = Input::get("zip");
            $address->lng = Input::get("lng");
            $address->lat = Input::get("lat");
            $address->zoom = Input::get("zoom");
            $address->save();
            
            $user->address_id = $address->id;
            $user->save();
            return $this->makeResponse($address, 200, "User Address set.");
        }
    }

}
