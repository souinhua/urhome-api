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
            "photo_id" => "required|numeric|exists:photo,id",
            "property_id"=>"required|numeric|exists:property,id"
        );
        $input = Input::all();
        $input['property_id'] = $propertyId;
        $validation = Validator::make($input, $rules);
        if ($validation->fails()) {
            return $this->makeFailResponse("Property photo linking failed due to validation error(s).", $validation->messages()->getMessages());
        } else {
            $photo = Photo::find(Input::get('photo_id'));
            
            $property = Property::find($propertyId);
            $property->photos()->attach($photo->id);
            $property->updated_by = Auth::id();
            $property->save();
            
            return $this->makeSuccessResponse("Property Photo linked successfully", $photo);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        //
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

}
