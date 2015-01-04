<?php

class PropertyPhotoController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($propertyId) {
        if ($property = Property::find($propertyId)) {
            $photos = $property->photos;
            return $this->makeSuccessResponse("Property Photos fetched", $photos->toArray());
        } else {
            return $this->makeFailResponse("Property does not exist");
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store($propertyId) {
        $rules = array(
            "photo" => "required|cloudinary_photo",
        );

        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeFailResponse("Property photo linking failed due to validation error(s).", $validation->messages()->getMessages());
        } else {
            if ($property = Property::find($propertyId)) {
                $data = Input::get('photo');
                $photo = PhotoManager::createCloudinary($data['public_id'], $property, Input::get('caption'), $data);

                if (!is_null($property->photo)) {
                    $property->photo->delete();
                }

                $property->photos()->attach($photo->id);
                $property->updated_by = Auth::id();
                $property->save();

                return $this->makeSuccessResponse("Photo upload of Property (ID = $propertyId) was successful", $photo->toArray());
            } else {
                return $this->makeFailResponse("Property does not exist");
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($propertyId, $photoId) {
        if($this->entityExists("property", $propertyId) && $this->entityExists("photo", $photoId)) {
            $photo = Photo::find($photoId);
            return $this->makeSuccessResponse("Photo resource fetched.", $photo->toArray());
        }
        else {
            return $this->makeFailResponse("Photo resource does not exist");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        //
    }

    public function count($propertyId) {
        if ($this->entityExists("property", $propertyId)) {
            $count = Property::find($propertyId)->photos->count();
            return $this->makeSuccessResponse("Photos count fetched", $count);
        } else {
            return $this->makeFailResponse("Property does not exist.");
        }
    }

}
