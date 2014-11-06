<?php

class AmenityController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $rules = array(
            "photo" => "image",
            "name" => "required|max:64",
            "description" => "required",
            "property" => "required|exists:property,id"
        );
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeFailResponse("Amenity creation failed due to validation errors.", $validation->messages()->getMessages());
        } else {
            $amenity = new Amenity();
            $amenity->name = Input::get('name');
            $amenity->description = Input::get('description');
            $amenity->property_id = Input::get('property');
            $amenity->save();

            return $this->makeSuccessResponse("Amenity created", $amenity->toArray());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        if ($amenity = Amenity::with('photo')->find($id)) {
            return $this->makeSuccessResponse("Amenity (ID = $id) fetched", $amenity->toArray());
        } else {
            return $this->makeFailResponse("Amenity does not exist");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        if ($amenity = Amenity::find($id)) {
            $amenity->name = Input::get('name', $amenity->name);
            $amenity->description = Input::get('description', $amenity->description);
            $amenity->save();

            return $this->makeSuccessResponse("Amenity (ID = $amenity->id) updated", $amenity->toArray());
        } else {
            return $this->makeFailResponse("Amenity does not exist");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        if ($amenity = Amenity::find($id)) {
            $amenity->delete();
            return $this->makeSuccessResponse("Amenity deleted", $amenity->toArray());
        } else {
            return $this->makeFailResponse("Amenity does not exist");
        }
    }

    public function savePhoto($id) {
        $rules = array(
            "photo" => "required|image"
        );
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            
        } else {
            $amenity = Amenity::find($id);
            $uploadedPhoto = Input::file('photo');
            
            $extension = $uploadedPhoto->getClientOriginalExtension();
            $fileName = $amenity->id . '_' . Auth::user()->id . '_' . time() . "." . $extension;

            $propertyDir = public_path() . "/uploads/properties/$amenity->property_id/amenities";
            if (!File::isDirectory($propertyDir)) {
                File::makeDirectory($propertyDir, 0775, true);
            }

            $destinationPath = public_path() . "/uploads/properties/$amenity->property_id/amenities";
            $uploadedPhoto->move($destinationPath, $fileName);

            $photo = new Photo();
            $photo->path = "$destinationPath/$fileName";
            $photo->url = URL::to("uploads/properties/$amenity->property_id/amenities/$fileName");
            $photo->uploaded_by = Auth::user()->id;
            $photo->save();

            if (!is_null($amenity->photo)) {
                $amenity->photo->delete();
            }
            $amenity->photo_id = $photo->id;
            $amenity->save();

            return $this->makeSuccessResponse("Photo upload for Amenity (ID = $id) was successful", $photo->toArray());
        }
    }

}
