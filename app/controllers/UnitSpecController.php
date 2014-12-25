<?php

class UnitSpecController extends \BaseController {

    /**
     * Fetch Spec resources of a Unit resource
     * 
     * @param int $unitId
     * @return Response
     */
    public function index($unitId) {
        if ($unit = Unit::find($unitId)) {
            $specs = $unit->specs;
            return $this->makeSuccessResponse("Unit specs fetched.", $specs->toArray());
        } else {
            return $this->makeFailResponse("Unit does not exist.");
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
                return $this->makeFailResponse("Spec creation failed due to validation error(s).", $validation->messages()->getMessages());
            } else {
                $spec = new Spec();
                $spec->variable = Input::get("variable");
                $spec->value = Input::get("value");
                $spec->save();
                
                $unit->specs()->attach($spec->id);
                $unit->save();
                return $this->makeSuccessResponse("Unit Spec created.", $spec->toArray());
            }
        } else {
            return $this->makeFailResponse("Unit does not exist.");
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

                return $this->makeSuccessResponse("Unit Spec updated.", $spec->toArray());
            } else {
                return $this->makeFailResponse("Spec does not exist.");
            }
        } else {
            return $this->makeFailResponse("Unit does not exist.");
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
        if ($unit = Unit::find($unitId) && ($this->entityExists("spec", $specId))) {
            $unit->specs()->detach($specId);
            $spec = Spec::find($specId);
            $spec->delete();

            return $this->makeSuccessResponse("Unit Spec deleted.", $spec->toArray());
        } else {
            return $this->makeFailResponse("Unit does not exist.");
        }
    }

}
