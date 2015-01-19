<?php

/**
 * Description of PropertyAmenityController
 *
 * @author Janssen Canturias
 */
class PropertyAmenityController extends BaseController {

    /**
     * Retrieve the amenity resources of a property resource
     * 
     * @param int $propertyId
     * @return Response
     */
    public function index($propertyId) {
        if ($property = Property::with('amenities')->find($propertyId)) {
            $amenities = $property->amenities;
            return $this->makeResponse($amenities, 200, "Property Amenities resources fetched.");
        } else {
            return $this->makeResponse(null, 404, "Property resource not found.");
        }
    }
    
    /**
     * Returns a Property Amenity resource.
     * 
     * @param int $propertyId
     * @param int $amenityId
     * @return Response
     */
    public function show($propertyId, $amenityId) {
        if ($amenity = Property::find($propertyId)->amenities()->with('photo')->find($amenityId)) {
            return $this->makeResponse($amenity, 200, "Property Amenities resource fetched.");
        } else {
            return $this->makeResponse(null, 404, "Property Amenity resource not found.");
        }
    }

    /**
     * Stores an amenity resource for a property resource
     * 
     * @param id $propertyId
     * @return Response
     */
    public function store($propertyId) {
        if ($this->entityExists('property', $propertyId)) {
            $rules = array(
                "name" => "required|max:64",
                "description" => "required",
                "photo" => "required|cloudinary_photo"
            );
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeResponse($validation->messages(), 400, "Request failed in Amenity resource validation.");
            } else {
                $amenity = new Amenity();
                $amenity->name = Input::get("name");
                $amenity->description = Input::get("description");
                $amenity->property_id = $propertyId;
                $amenity->save();
                
                if(Input::has('photo')) {
                    $this->photo($propertyId, $amenity->id);
                }
                
                return $this->makeResponse($amenity, 201, "Property Amenity resource created.");
            }
        } else {
            return $this->makeResponse(null, 404, "Property resource not found.");
        }
    }
    
    /**
     * Updates an Amenity resource.
     * 
     * @param int $propertyId
     * @param int $amenityId
     * @return Response
     */
    public function update($propertyId, $amenityId) {
        if($amenity = Property::find($propertyId)->amenities()->find($amenityId)) {
            $rules = array(
                "name" => "max:64",
                "photo" => "cloudinary_photo"
            );
            
            $validation = Validator::make(Input::all(), $rules);
            if($validation->fails()) {
                return $this->makeResponse($validation->messages(), 400, "Request failed in Amenity resource validation.");
            }
            else {
                $fields = array("name", "description");
                foreach($fields as $field) {
                    if($this->hasInput($field)) {
                        $amenity->$field = Input::get($field);
                    }
                }
                $amenity->save();
                
                if(Input::has("photo")) {
                    $this->photo($propertyId, $amenity->id);
                }
                return $this->makeResponse($amenity, 200, "Property Amenity resource updated.");
            }
        }
        else {
            return $this->makeResponse(null, 404, "Property Amenity resource not found.");
        }
    }
    
    /**
     * Deletes a Amenity resource
     * 
     * @param int $propertyId
     * @param int $amenityId
     * @return Response
     */
    public function destroy($propertyId, $amenityId) {
        if ($amenity = Property::find($propertyId)->amenities()->find($amenityId)) {
            $amenity->delete();
            return $this->makeResponse(null, 204, "Property Amenities resource deleted.");
        } else {
            return $this->makeResponse(null, 404, "Property Amenity resource not found.");
        }
    }
    

    /**
     * Stores a cloudinary resource photo for an Amenity Resource.
     * 
     * @param int $propertyId
     * @param int $amenityId
     * @return Response
     */
    public function photo($propertyId, $amenityId) {
        if ($amenity = Property::find($propertyId)->amenities()->find($amenityId)) {
            $rules = array(
                "photo" => "required|cloudinary_photo",
                "caption" => "max: 256"
            );
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                
            } else {
                
                $data = Input::get('photo');
                $photo = PhotoManager::createCloudinary($data['public_id'], $amenity, Input::get('caption'), $data);
                if (!is_null($amenity->photo)) {
                    $amenity->photo->delete();
                }
                $amenity->photo_id = $photo->id;
                $amenity->save();

                return $this->makeResponse($photo, 201, "Amenity Photo resource created.");
            }
        } else {
            return $this->makeResponse(null, 404, "Property Amenity resource not found.");
        }
    }

}
