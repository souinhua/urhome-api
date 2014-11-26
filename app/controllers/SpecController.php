<?php

class SpecController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $specs = Spec::all();
        return $this->makeSuccessResponse("Spec Resources fetched", $specs->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        if ($spec = Spec::find($id)) {
            return $this->makeSuccessResponse("Spec (ID = $id) resource fetched.", $spec->toArray());
        } else {
            return $this->makeFailResponse("Spec does not exist.");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        if ($spec = Spec::find($id)) {
            foreach(array('varaible', 'value') as $field) {
                if(Input::has($field)) {
                    $spec->$field = Input::get($field);
                }
            }
            $spec->save();
            return $this->makeSuccessResponse("Spec (ID = $id) resource updated.", $spec->toArray());
        } else {
            return $this->makeFailResponse("Spec does not exist.");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        if ($spec = Spec::find($id)) {
            $spec->delete();
            return $this->makeSuccessResponse("Spec (ID = $id) resource deleted.", $spec->toArray());
        } else {
            return $this->makeFailResponse("Spec does not exist.");
        }
    }

}
