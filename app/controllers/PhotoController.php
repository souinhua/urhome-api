<?php

class PhotoController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $query = Photo::with('uploader');
        
        $limit = Input::get('limit', 1000);
        $offset = Input::get('offset', 0);
        
        $photos = $query->take($limit)->skip($offset)->get();
        return $this->makeSuccessResponse("All photos fetched.", $photos->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $rules = array(
            "photo" => "required|image",
            "caption" => "max:256",
            "type" => "required|in:property,unit,user,developer,content",
            "type_id" => "required|numeric"
        );

        if (Input::get('type') == 'property') {
            $rules['type_id'] = "required|exists:property,id";
        } else if (Input::get('type' == 'unit')) {
            $rules['type_id'] = "required|exists:unit,id";
        } else if (Input::get('type' == 'user')) {
            $rules['type_id'] = "required|exists:user,id";
        } else if (Input::get('type' == 'developer')) {
            $rules['type_id'] = "required|exists:developer,id";
        } else if (Input::get('type' == 'content')) {
            $rules['type_id'] = "required|exists:content,id";
        } else if (Input::get('type' == 'amenity')) {
            $rules['type_id'] = "required|exists:amenity,id";
        }

        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeFailResponse("Photo upload failed due to validation error(s)", $validation->messages()->getMessages());
        } else {
            $extension = Input::file('photo')->getClientOriginalExtension();

            $type = Input::get("type");
            $type_id = Input::get("type_id");

            $time = time();
            $userId = Auth::id();
            if ($type == 'property') {
                $property = Property::find($type_id);
                $fileName = "$property->id-$userId-$time.$extension";
                $uploadPath = "uploads/properties/$property->id";
            } else if ($type == 'unit') {
                $unit = Unit::find($type_id);
                $fileName = "$unit->id-$userId-$time.$extension";
                $uploadPath = "uploads/properties/$unit->property_id/units/$unit->id";
            } else if ($type == 'user') {
                $user = User::find($type_id);
                $fileName = "$user->id-$userId-$time.$extension";
                $uploadPath = "uploads/users";
            } else if ($type == 'amenity') {
                $amenity = Amenity::find($type_id);
                $fileName = "$amenity->id-$userId-$time.$extension";
                $uploadPath = "uploads/properties/$amenity->property_id/amenities";
            } else if($type == 'developer') {
                $developer = Developer::find($type_id);
                $fileName = "$developer->id-$userId-$time.$extension";
                $uploadPath = "uploads/developers";
            } else if($type == 'content') {
                $content = Content::find($type_id);
                $fileName = "$content->id-$userId-$time.$extension";
                $uploadPath = "uploads/contents";
            }

            $destinationPath = public_path($uploadPath);
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $file = Input::file('photo')->move($destinationPath, $fileName);
            if ($file) {
                $photo = new Photo();
                $photo->path = "$destinationPath/$fileName";
                $photo->url = URL::to("$uploadPath/$fileName");
                $photo->uploaded_by = Auth::id();
                $photo->caption = Input::get('caption', null);
                $photo->save();

                return $this->makeSuccessResponse("Photo uploaded successfully.", $photo->toArray());
            } else {
                return $this->makeFailResponse("Upload Error");
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        if ($photo = Photo::find($id)) {
            return $this->makeSuccessResponse("Photo fetched.", $photo->toArray());
        } else {
            return $this->makeFailResponse("Photo does not exist.");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        $rules = array(
            "caption" => "max:256", 
            "id" => "required|numeric|exists:photo,id"
        );
        $input = Input::all();
        $input['id'] = $id;
        
        $validation = Validator::make($input, $rules);
        if($validation->fails()) {
            return $this->makeFailResponse("Update photo failed due to validation error(s).", $validation);
        }
        else {
            $photo = Photo::find($id);
            $photo->caption = Input::get('caption');
            $photo->save();
            
            return $this->makeSuccessResponse("Photo (ID = $id) updated successfully", $photo->toArray());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        if($photo = Photo::find($id)) {
            $photo->delete();
            return $this->makeSuccessResponse("Photo (ID = $id) deleted.");
        }
        else {
            return $this->makeFailResponse("Photo does not exist");
        }
    }

    /**
     * Display the specified Photo resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function display($id) {
        if ($photo = Photo::find($id)) {

            $file = new \Symfony\Component\HttpFoundation\File\File($photo->path);
            $mimeType = $file->getMimeType();

            $blob = File::get($photo->path);
            $response = Response::make($blob, 200);
            $response->header("Content-type", $mimeType);
            return $response;
        } else {
            return $this->makeFailResponse("Photo (ID = $id) does not exist.");
        }
    }
}
