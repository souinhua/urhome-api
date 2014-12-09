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
    
}
