<?php

class PropertyUnitController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($propertyId) {
        $with = Input::get('with', array('details'));
        if ($property = Property::with($with)->find($propertyId)) {
            $units = $property->units;
            return $this->makeSuccessResponse("Property Units resource fetched.", $units->toArray());
        } else {
            return $this->makeFailResponse("Property does not exist.");
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store($propertyId) {
        if ($property = Property::find($propertyId)) {
            $rules = array(
                "name" => "required|max:64",
                "description" => "required",
                "bed" => "required|numeric",
                "bath" => "required|numeric",
                "parking" => "required|numeric",
                "area" => "required|numeric",
                "furnish" => "required|in:none,semi,full",
                "min_price" => "required|numeric",
                "max_price" => "numeric"
            );

            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeFailResponse("Unit creation failed due to validation errors.", $validation->messages()->getMessages());
            } else {
                $unit = new Unit();
                $unit->name = Input::get("name");
                $unit->description = Input::get("description");
                $unit->property_id = $property->id;

                $details = new CommonDetails();
                $details->bed = Input::get("bed");
                $details->bath = Input::get("bath");
                $details->parking = Input::get("parking");
                $details->area = Input::get("area");
                $details->furnish = Input::get("furnish");
                $details->min_price = Input::get("min_price");
                $details->max_price = Input::get("max_price");
                $details->save();

                $unit->common_details_id = $details->id;
                $unit->save();
                return $this->makeSuccessResponse("Property Units resource created.", $unit->toArray());
            }
        } else {
            return $this->makeFailResponse("Property does not exist.");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($propertyId, $unitId) {
        $with = Input::get("with", array('details'));
        
        if ($property = Property::find($propertyId)) {
            $unit = $property->units()->with($with)->find($unitId);
            return $this->makeSuccessResponse("Property Unit resource fetched.", $unit->toArray());
        } else {
            return $this->makeFailResponse("Property does not exist.");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($propertyId, $unitId) {
        if ($property = Property::find($propertyId)) {
            if ($unit = $property->units()->find($unitId)) {
                foreach(array("name", "description") as $field) {
                    if(Input::has($field)) {
                        $unit->$field = Input::get($field);
                    }
                }
                $unit->save();
                return $this->makeSuccessResponse("Unit (ID = $unitId) updated", $unit->toArray());
            }
            else {
                return $this->makeFailResponse("Unit does not exist.");
            }
        }
        else {
            return $this->makeFailResponse("Property does not exist.");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($propertyId, $unitId) {
        if ($property = Property::find($propertyId)) {
            if ($unit = $property->units()->find($unitId)) {
                $property->units()->detach($unit->id);
                $unit->delete();
                
                $property->updated_by = Auth::id();
                $property->save();
                
                return $this->makeSuccessResponse("Unit (ID = $unitId) deleted.");
            }
            else {
                return $this->makeFailResponse("Unit does not exist.");
            }
        }
        else {
            return $this->makeFailResponse("Property does not exist.");
        }
    }

    public function mainPhoto($propertyId, $unitId) {
        $rules = array(
            "photo" => "required|image", 
            "caption" => "max:256"
        );
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeFailResponse("Photo upload failed due to validation errors.", $validation->messages()->getMessages());
        } else {
            if ($property = Property::find($propertyId) && ($unit = Unit::find($unitId))) {
                $uploadedPhoto = Input::file('photo');
                $photo = PhotoManager::create($uploadedPhoto, "unit", $unit->id, Input::get("caption", null));
                if (!is_null($unit->photo)) {
                    $unit->photo->delete();
                }
                $unit->photo_id = $photo->id;
                $unit->save();

//                $property->updated_by = Auth::id();
//                $property->save();

                return $this->makeSuccessResponse("Photo upload of Unit (ID = $unitId) was successful", $photo->toArray());
            } else {
                return $this->makeFailResponse("Property does not exist");
            }
        }
    }

}
