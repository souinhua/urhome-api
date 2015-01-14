<?php

class UnitFeatureController extends \BaseController {

    /**
     * Fetch Feature resources of a Unit resource
     * 
     * @param int $unitId
     * @return Response
     */
    public function index($unitId) {
        if ($unit = Unit::with("features")->find($unitId)) {
            $features = $unit->features;
            return $this->makeResponse($features, 200, "Unit Feature resources fetched.");
        } else {
            return $this->makeResponse(null, 404, "Unit does not exist.");
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
                return $this->makeResponse($validation->messages(), 400, "Request failed due to Unit Feature validation error(s).");
            }
            else {
                $feature = new Feature();
                $feature->name = Input::get("name");
                $feature->description = Input::get("description");
                $feature->save();
                
                $unit->features()->attach($feature->id);
                $unit->save();
                return $this->makeResponse($feature, 201, "Unit Feature resource created.");
            }
            
        } else {
            return $this->makeResponse(null, 404, "Unit resource not found.");
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
                
                return $this->makeResponse($feature, 200, "Unit Feature resource updated.");
            }
            else {
                return $this->makeResponse(null, 404, "Feature does not exist.");
            }
        } 
        else {
            return $this->makeResponse(null, 404, "Unit does not exist.");
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
        if(($unit = Unit::find($unitId)) && ($this->entityExists("feature", $featureId))) {
            $unit->features()->detach($featureId);
            $feature = Feature::find($featureId);
            $feature->delete();
            
            return $this->makeResponse(null, 204, "Unit Feature resource deleted.");
        }
        else {
            return $this->makeResponse(null, 404, "Unit Feature resource not found..");
        }
    }

}
