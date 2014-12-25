<?php

class UnitFeatureController extends \BaseController {

    /**
     * Fetch Feature resources of a Unit resource
     * 
     * @param int $unitId
     * @return Response
     */
    public function index($unitId) {
        if ($unit = Unit::find($unitId)) {
            $features = $unit->features;
            return $this->makeSuccessResponse("Unit features fetched.", $features->toArray());
        } else {
            return $this->makeFailResponse("Unit does not exist.");
        }
    }

    /**
     * Store Feature resource for a Unit resource
     * 
     * @param int $unitId
     * @return Response
     */
    public function store($unitId) {
        if ($unit = Unit::find($unitId)) {
            $rules = array(
                'name' => 'required|max:128'
            );
            $validation = Validator::make(Input::all(), $rules);
            if($validation->fails()) {
                return $this->makeFailResponse("Feature creation failed due to validation error(s).", $validation->messages()->getMessages());
            }
            else {
                $feature = new Feature();
                $feature->name = Input::get("name");
                $feature->description = Input::get("description");
                $feature->save();
                
                $unit->features()->attach($feature->id);
                $unit->save();
                return $this->makeSuccessResponse("Unit Feature created.", $feature->toArray());
            }
            
        } else {
            return $this->makeFailResponse("Unit does not exist.");
        }
    }
    
    /**
     * Update Feature Resource of a Unit
     * 
     * @param int $unitId
     * @param int $featureId
     * @return Response
     */
    public function update($unitId, $featureId) {
        if($unit = Unit::find($unitId)) {
            $feature = $unit->features->find($featureId);
            if($feature) {
                foreach(array("name", "description") as $field) {
                    if($this->hasInput($field)) {
                        $feature->$field = Input::get($field);
                    }
                }
                $feature->save();
                
                return $this->makeSuccessResponse("Unit Feature updated.", $feature->toArray());
            }
            else {
                return $this->makeFailResponse("Feature does not exist.");
            }
        } 
        else {
            return $this->makeFailResponse("Unit does not exist.");
        }
    }
    
    /**
     * Deletes a Feature of a Unit
     * 
     * @param int $unitId
     * @param int $featureId
     * @return Response
     */
    public function destroy($unitId, $featureId) {
        if($unit = Unit::find($unitId) && ($this->entityExists("feature", $featureId))) {
            $unit->features()->detach($featureId);
            $feature = Feature::find($featureId);
            $feature->delete();
            
            return $this->makeSuccessResponse("Unit Feature deleted.", $feature->toArray());
        }
        else {
            return $this->makeFailResponse("Unit does not exist.");
        }
    }

}
