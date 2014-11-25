<?php

/**
 * Description of PropertyFeatureController
 *
 * @author Janssen Canturias
 */
class PropertyFeatureController extends BaseController {
    
    /**
     * Return Features of a Property
     *
     * @param $propertyId Property ID
     * @return Response
     */
    public function index($propertyId) {
        if($property = Property::find($propertyId)) {
            $features = $property->features;
            return $this->makeSuccessResponse("Features of Property (ID = $propertyId) fetched.", $features->toArray());
        }
        else {
            return $this->makeFailResponse("Property does not exist.");
        }
    }
    
    /**
     * Store Feature of a Property
     *
     * @param $propertyId Property ID
     * @return Response
     */
    public function store($propertyId) {
        $response = null;
        if($property = Property::find($propertyId)) {
            $rules = array(
                'name' => 'required|max:128'
            );
            $validation = Validator::make(Input::all(), $rules);
            if($validation->fails()) {
                $response = $this->makeFailResponse("Feature creation could not complete due to validation error(s).", $validation->messages()->getMessages());
            }
            else {
                $feature = new Feature();
                $feature->name = Input::get("name");
                $feature->description = Input::get("description");
                $feature->save();
                
                $property->features()->attach($feature->id);
                $property->save();
                $response = $this->makeSuccessResponse("Property Feature created.", $feature->toArray());
            }
        }
        else {
            $response = $this->makeFailResponse("Property does not exist.");
        }
        return $response;
    }
    
    /**
     * Update Feature of a Property
     *
     * @param $propertyId Property ID
     * @param $featureId Feature ID
     * @return Response
     */
    public function update($propertyId, $featureId) {
        $rules = array(
            "name" => "required|max:128"
        );
        $validation = Validator::make(Input::all(), $rules);
        if($validation->fails()) {
            return $this->makeFailResponse("Feature creation could not complete due to validation error(s).", $validation->messages()->getMessages());
        }
        
        $response = null;
        if(($property = Property::find($propertyId)) && ($feature = Feature::find($featureId))) {
            $feature->name = Input::get("name");
            $feature->description = Input::get("description", $feature->description);
            $feature->save();
            return $this->makeSuccessResponse("Feature (ID=$featureId) updated.", $feature);
        }
        else {
            $response = $this->makeFailResponse("Property Feature does not exist.");
        }
        return $response;
    }
}
