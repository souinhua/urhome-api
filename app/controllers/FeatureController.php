<?php

class FeatureController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $features = Feature::all();
        return $this->makeSuccessResponse("Features fetched", $features);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $rules = array(
            "name" => "required|max:128",
        );
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeFailResponse("Feature creation failed due to validation errors.", $validation->messages()->getMessages());
        } else {
            $feature = new Feature();
            $feature->name = Input::get('name');
            $feature->desciption = Input::get('desciption', null);
            $feature->save();
            return $this->makeSuccessResponse("Feature created", $feature->toArray());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        if ($feature = Feature::find($id)) {
            return $this->makeSuccessResponse("Feature created", $feature->toArray());
        } else {
            return $this->makeFailResponse("Feature (ID = $id) does not exist");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        if ($feature = Feature::find($id)) {
            foreach(array('name','description') as $field) {
                if(Input::has($field)) {
                    $feature->$field = Input::get($field);
                }
            }
            $feature->save();
            return $this->makeSuccessResponse("Feature (ID = $id) updated", $feature->toArray());
        } else {
            return $this->makeFailResponse("Feature (ID = $id) does not exist");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        if ($feature = Feature::find($id)) {
            $feature->delete();
            return $this->makeSuccessResponse("Feature (ID = $id) deleted");
        } else {
            return $this->makeFailResponse("Feature (ID = $id) does not exist");
        }
    }

}
