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
        if ($property = Property::with('features')->find($propertyId)) {
            $features = $property->features;
            return $this->makeResponse($features, 200, "Features resource of Property (ID = $propertyId) fetched.");
        } else {
            return $this->makeResponse(null, 404, "Property resource not found.");
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
        if ($property = Property::find($propertyId)) {
            $rules = array(
                'name' => 'required|max:128'
            );
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                $response = $this->makeResponse(null, 404, "Feature creation could not complete due to validation error(s).");
            } else {
                $feature = new Feature();
                $feature->name = Input::get("name");
                $feature->description = Input::get("description");
                $feature->property_id = $property->id;
                $feature->save();

                $property->updated_by_id = Auth::id();
                $property->save();
                $response = $this->makeResponse($feature, 201, "Property Feature created.");
            }
        } else {
            $response = $this->makeResponse(null, 404, "Property resource does not exist.");
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
        if ($validation->fails()) {
            return $this->makeResponse($validation->messages(), 404, "Feature creation could not complete due to validation error(s).");
        }

        $response = null;
        if (($property = Property::find($propertyId)) && ($feature = Feature::find($featureId))) {
            $feature->name = Input::get("name");
            $feature->description = Input::get("description", $feature->description);
            $feature->property_id = $property->id;
            $feature->save();
            return $this->makeSuccessResponse($feature, 200, "Feature (ID=$featureId) updated.");
        } else {
            $response = $this->makeResponse(null, 404, "Property Feature does not exist.");
        }
        return $response;
    }

    /**
     * Deletes a Property Feature resource.
     * 
     * @param int $propertyId
     * @param int $featureId
     * @return Response
     */
    public function destroy($propertyId, $featureId) {
        if ($feature = Property::find($propertyId)->features()->find($featureId)) {

            $feature->delete();
            return $this->makeResponse(null, 204, "Property Feature resource deleted.");
        } else {
            return $this->makeResponse(null, 404, "Property Feature resource not found.");
        }
    }

}
