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
                "min_price" => "required|numeric",
                "max_price" => "numeric",
            );

            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeResponse($validation->messages(), 400, "Request failed in Property Unit resource validation.");
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
                $details->area = Input::get("min_price");
                $details->furnish = Input::get("max_price");
                $details->save();

                $unit->common_details_id = $details->id;
                $unit->save();
                
                $this->updatePricing($property, Input::get("min_price",null), Input::get("max_price",null));

                return $this->makeResponse($unit, 201, "Property Units resource created.");
            }
        } else {
            return $this->makeResponse(null, 404, "Property resource not found.");
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
            return $this->makeResponse($unit, 200, "Property Unit resource fetched.");
        } else {
            return $this->makeResponse(null, 404, "Property resource not found.");
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
                foreach (array("bed", "bath", "parking", "furnish", "area", "min_price", "max_price") as $dField) {
                    if ($this->hasInput($dField)) {
                        $details->$dField = Input::get($dField);
                    }
                }

                $details->save();
                $unit->save();
                
                $this->updatePricing($property, Input::get("min_price",null), Input::get("max_price",null));
                return $this->makeResponse($unit, 200, "Unit resource (ID = $unitId) updated");
            } else {
                return $this->makeResponse(null, 404, "Unit resource not found.");
            }
        } else {
            return $this->makeResponse(null, 404, "Property resource not found.");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($propertyId, $unitId) {
        if ($unit = Property::find($propertyId)->units()->find($unitId)) {
            $unit->delete();
            return $this->makeResponse(null, 204, "Property Unit (ID = $unitId) resource deleted.");
        } else {
            return $this->makeResponse(null, 404, "Property Unit resource not found.");
        }
    }

    /**
     * Creates a Cloudinary Photo resource for a Unit.
     * 
     * @param int $propertyId
     * @param int $unitId
     * @return Response
     */
    public function mainPhoto($propertyId, $unitId) {
        $rules = array(
            "photo" => "required|cloudinary_photo",
            "caption" => "max:256"
        );
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeResponse($validation->messages(), 400, "Resource failed in Photo resource validation.");
        } else {
            if ($property = Property::find($propertyId) && ($unit = Unit::find($unitId))) {

                if (!is_null($unit->photo)) {
                    $unit->photo->delete();
                }

                $cloudinaryData = Input::get("photo");
                $photo = PhotoManager::createCloudinary($cloudinaryData['public_id'], $unit, Input::get('caption'), $cloudinaryData);

                $unit->photo_id = $photo->id;
                $unit->save();

                return $this->makeResponse($photo, 201, "Photo resource of Unit (ID = $unitId) created.");
            } else {
                return $this->makeResponse(null, 404, "Property Unit resource not found.");
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
        $property = Property::find($propertyId);
        if ($unit = $property->units()->find($unitId)) {

            $rules = array(
                "bed" => "numeric",
                "bath" => "numeric",
                "parking" => "numeric",
                "area" => "numeric",
                "furnish" => "in:full,semi,none",
                "min_price" => "numeric",
                "max_price" => "numeric"
            );

            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeResponse($validation->messages(), 400, "Request failed in Property Unit resource validation.");
            } else {
                $details = $unit->details;
                if (is_null($details)) {
                    $details = new CommonDetails();
                }

                foreach ($rules as $field => $rule) {
                    if ($this->hasInput($field)) {
                        $details->$field = Input::get($field);
                    }
                }
                $details->save();
                $unit->common_details_id = $details->id;

                $unit->save();
                $this->updatePricing($property, Input::get("min_price",null), Input::get("max_price",null));

                return $this->makeResponse($details, 200, "Property Unit resource saved.");
            }
        } else {
            return $this->makeResponse(null, 404, "Property Unit resource not found.");
        }
    }

    /**
     * Updates Price of a Property if property.unit_price is set.
     * 
     * @param Property $property
     * @param number $minPrice
     * @param number $maxPrice
     */
    private function updatePricing(Property $property, $minPrice, $maxPrice) {
        if ($property->unit_price) {
            $minPrice = Input::get("min_price");
            foreach ($property->units as $unit) {
                if ($unit->details->min_price < $minPrice) {
                    $minPrice = $unit->details->min_price;
                }
            }

            $maxPrice = Input::get("max_price");
            foreach ($property->units as $unit) {
                if ($unit->details->max_price > $maxPrice) {
                    $maxPrice = $unit->details->max_price;
                }
            }
            if(!is_null($property->details)) {
                $details = $property->details;
            }
            else {
                $details = new CommonDetails();
            }
            
            $details->min_price = $minPrice;
            $details->max_price = $maxPrice;
            $details->save();
        }
    }

}
