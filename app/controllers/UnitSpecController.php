<?php

class UnitSpecController extends \BaseController {

    /**
     * Fetch Spec resources of a Unit resource
     * 
     * @param int $unitId
     * @return Response
     */
    public function index($unitId) {
        if ($unit = Unit::with("specs")->find($unitId)) {
            $specs = $unit->specs;
            return $this->makeResponse($specs, 200, "Unit Specs fetched.");
        } else {
            return $this->makeResponse(null, 404, "Unit does not exist.");
        }
    }

    /**
     * Store Spec resource for a Unit resource
     * 
     * @param int $unitId
     * @return Response
     */
    public function store($unitId) {
        if ($unit = Unit::find($unitId)) {
            $rules = array(
                'variable' => 'required|max:32',
                'value' => 'required|max:64'
            );
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeResponse($validation->messages(), 404, "Request failed due to Unit Spec resource validation.");
            } else {
                $spec = new Spec();
                $spec->variable = Input::get("variable");
                $spec->value = Input::get("value");
                $spec->save();
                
                $unit->specs()->attach($spec->id);
                $unit->save();
                return $this->makeResponse($unit, 201, "Unit Spec response created.");
            }
        } else {
            return $this->makeResponse(null, 404, "Unit does not exist.");
        }
    }

    /**
     * Update Spec Resource of a Unit
     * 
     * @param int $unitId
     * @param int $specId
     * @return Response
     */
    public function update($unitId, $specId) {
        if ($unit = Unit::find($unitId)) {
            $spec = $unit->specs->find($specId);
            if ($spec) {
                foreach (array("variable", "value") as $field) {
                    if ($this->hasInput($field)) {
                        $spec->$field = Input::get($field);
                    }
                }
                $spec->save();

                return $this->makeSuccessResponse($spec, 200, "Unit Spec resource updated.");
            } else {
                return $this->makeResponse(null, 404, "Spec does resource not found.");
            }
        } else {
            return $this->makeResponse(null, 404, "Unit resource not found.");
        }
    }

    /**
     * Deletes a Spec of a Unit
     * 
     * @param int $unitId
     * @param int $specId
     * @return Response
     */
    public function destroy($unitId, $specId) {
        if (($unit = Unit::find($unitId)) && ($this->entityExists("spec", $specId))) {
            $unit->specs()->detach($specId);
            $spec = Spec::find($specId);
            $spec->delete();

            return $this->makeResponse(null, 204, "Unit Spec resource deleted.");
        } else {
            return $this->makeResponse(null, 404, "Unit does not exist.");
        }
    }

}
