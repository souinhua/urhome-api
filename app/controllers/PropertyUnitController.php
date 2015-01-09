<?php

class PropertyUnitController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($propertyId) {
        $with = Input::get('with', array('details'));
        if ($this->entityExists("property", $propertyId)) {
            $units = Unit::with($with)->where("property_id", "=", $propertyId)->get();
            return $this->makeResponse($units, 200, "Unit resources fetched.");
        } else {
            return $this->makeResponse(null, 404, "Property resource not found.");
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
                foreach (array("name", "description") as $field) {
                    if (Input::has($field)) {
                        $unit->$field = Input::get($field);
                    }
                }

                $details = $unit->details;
                foreach (array("bed", "bath", "parking", "furnish", "area") as $dField) {
                    if ($this->hasInput($dField)) {
                        $details->$dField = Input::get($dField);
                    }
                }

                $details->save();
                $unit->save();
                return $this->makeSuccessResponse("Unit (ID = $unitId) updated", $unit->toArray());
            } else {
                return $this->makeFailResponse("Unit does not exist.");
            }
        } else {
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
            } else {
                return $this->makeFailResponse("Unit does not exist.");
            }
        } else {
            return $this->makeFailResponse("Property does not exist.");
        }
    }

    public function mainPhoto($propertyId, $unitId) {
        $rules = array(
            "photo" => "required|cloudinary_photo",
            "caption" => "max:256"
        );
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeFailResponse("Photo upload failed due to validation errors.", $validation->messages()->getMessages());
        } else {
            if ($property = Property::find($propertyId) && ($unit = Unit::find($unitId))) {

                if (!is_null($unit->photo)) {
                    $unit->photo->delete();
                }

                $cloudinaryData = Input::get("photo");
                $photo = PhotoManager::createCloudinary($cloudinaryData['public_id'], $unit, Input::get('caption'), $cloudinaryData);

                $unit->photo_id = $photo->id;
                $unit->save();

                return $this->makeSuccessResponse("Photo upload of Unit (ID = $unitId) was successful", $photo->toArray());
            } else {
                return $this->makeFailResponse("Property does not exist");
            }
        }
    }

    /**
     * Updates or Create Details resource for a Unit resource.
     * 
     * @param int $propertyId
     * @param int $unitId
     * @return API Response
     */
    public function details($propertyId, $unitId) {
        if ($unit = Property::find($propertyId)->units()->find($unitId)) {

            $rules = array(
                "bed" => "numeric",
                "bath" => "numeric",
                "parking" => "numeric",
                "area" => "numeric",
                "furnish" => "in:full,semi,none"
            );

            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeResponse($validation->messages(), 400, "Request failed in Property Unit resource validation.");
            } else {
                $details = $unit->details;
                if (is_null($details)) {
                    $details = new CommonDetails();
                }
                
                foreach($rules as $field => $rule) {
                    if($this->hasInput($field)) {
                        $details->$field = Input::get($field);
                    }
                }
                $details->save();
                $unit->common_details_id = $details->id;
                
                $unit->save();
                
                return $this->makeResponse($details, 200, "Property Unit resource saved.");
            }
        } else {
            return $this->makeResponse(null, 404, "Property Unit resource not found.");
        }
    }

}
