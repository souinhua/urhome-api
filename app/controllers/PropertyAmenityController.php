<?php

/**
 * Description of PropertyAmenityController
 *
 * @author User
 */
class PropertyAmenityController extends BaseController{
    
    /**
     * Retrieve the amenity resources of a property resource
     * 
     * @param int $propertyId
     * @return Response
     */
    public function index($propertyId) {
        if($this->entityExists('property', $propertyId)) {
            $with = Input::get('with', array('photo'));
            
            $amenities = Amenity::with($with)->where("property_id", "=", $propertyId)->get();
            return $this->makeSuccessResponse("Amenities of Property (ID = $propertyId) feteched.", $amenities->toArray());
        }
        else {
            return $this->makeFailResponse("Property (ID = $propertyId) does not exists.");
        }
    }
    /**
     * Stores an amenity resource for a property resource
     * 
     * @param id $propertyId
     * @return Response
     */
    public function store($propertyId) {
        if($this->entityExists('property', $propertyId)) {
            $rules = array(
                "name" => "required|max:64", 
                "description" => "required"
            );
            $validation = Validator::make(Input::all(), $rules);
            if($validation->fails()) {
                return $this->makeFailResponse("Amenity creation failed due to validation errors.", $validation->messages()->getMessages());
            }
            else {
                $amenity = new Amenity();
                $amenity->name = Input::get("name");
                $amenity->description = Input::get("description");
                $amenity->property_id = $propertyId;
                $amenity->save();
                
                return $this->makeSuccessResponse("Amenity resource created", $amenity->toArray());
            }
        }
        else {
            return $this->makeFailResponse("Property (ID = $propertyId) does not exists.");
        }
    }
    
}
