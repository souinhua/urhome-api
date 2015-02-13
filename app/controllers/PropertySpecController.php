<?php

/**
 * Description of PropertySpecController
 *
 * @author Janssen Canturias
 */
class PropertySpecController extends BaseController {

    /**
     * Fetch Property Spec resources
     *
     * @return Response
     */
    public function index($propertyId) {
        if ($property = Property::find($propertyId)) {
            $specs = $property->specs;
            return $this->makeResponse($specs, 200, 'Property Specs resource fetched.');
        } else {
            return $this->makeResponse(null, 404, "Property resource not found.");
        }
    }

    /**
     * Store Property Spec resources
     *
     * @return Response
     */
    public function store($propertyId) {
        if ($property = Property::find($propertyId)) {
            $rules = array(
                "variable" => "required|max:32",
                "value" => "required|max:64"
            );

            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeResponse($validation->messages(), 400, "Request failed in Property Sepc resource validation.");
            } else {
                $spec = new Spec();
                $spec->variable = Input::get('variable');
                $spec->value = Input::get('value');
                $spec->property_id = $property->id;
                $spec->save();

                $property->updated_by_id = Auth::id();
                $property->save();

                return $this->makeResponse($spec, 201, "Property Specifications created");
            }
        } else {
            return $this->makeResponse(null, 404, "Property Spec resource not found.");
        }
    }

    /**
     * Update Property Spec Resource
     * 
     * @param $propertyId
     * @param $specId
     * 
     * @return Response
     */
    public function update($propertyId, $specId) {
        if (($property = Property::find($propertyId)) && ($spec = Spec::find($specId))) {
            $spec->variable = Input::get('variable', $spec->variable);
            $spec->value = Input::get('value', $spec->value);
            $spec->property_id = $property->id;
            $spec->save();

            $property->updated_by_id = Auth::id();
            $property->save();
            
            return $this->makeResponse($spec, 200, "Property Spec resource updated");
        } else {
            return $this->makeResponse(null, 404, "Property Specification resource not found.");
        }
    }
    
    /**
     * Delete Property Spec Resource
     * 
     * @param $propertyId
     * @param $specId
     * 
     * @return Response
     */
    public function destroy($propertyId, $specId) {
        if (($property = Property::find($propertyId)) && ($spec = Spec::find($specId))) {
            $property->specs()->detach($spec->id);
            $spec->delete();

            $property->updated_by = Auth::id();
            $property->save();
            
            return $this->makeResponse(null, 204, "Property Spec resource deleted.");
        } else {
            return $this->makeResponse(null, 404, "Property Specification does not exist.");
        }
    }

}
