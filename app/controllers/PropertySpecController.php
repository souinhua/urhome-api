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
            return $this->makeSuccessResponse("Property Specifications fetched", $specs->toArray());
        } else {
            return $this->makeFailResponse("Property does not exist");
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
                return $this->makeFailResponse("Spec Creation cannot be completed due to validation error(s).", $validation->messages()->getMessages());
            } else {
                $spec = new Spec();
                $spec->variable = Input::get('variable');
                $spec->value = Input::get('value');
                $spec->save();

                $property->specs()->attach($spec->id);
                $property->updated_by = Auth::id();
                $property->save();

                return $this->makeSuccessResponse("Property Specifications fetched", $spec->toArray());
            }
        } else {
            return $this->makeFailResponse("Property does not exist");
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
            $spec->save();

            $property->updated_by = Auth::id();
            $property->save();
            
            return $this->makeSuccessResponse("Property Spec updated", $spec->toArray());
        } else {
            return $this->makeFailResponse("Property Specification does not exist.");
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
            
            return $this->makeSuccessResponse("Property Spec deleted", $spec->toArray());
        } else {
            return $this->makeFailResponse("Property Specification does not exist.");
        }
    }

}
