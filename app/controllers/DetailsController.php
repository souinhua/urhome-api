<?php

class DetailsController extends \BaseController {

    private $fields;

    function __construct() {
        $this->fields = array(
            "bed",
            "bath",
            "parking",
            "area",
            "furnish",
            "min_price",
            "max_price"
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $details = CommonDetails::all();
        return $this->makeSuccessResponse("Details fetched", $details->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $rules = array(
            "bed" => "required|numeric",
            "bath" => "required|numeric",
            "parking" => "required|numeric",
            "area" => "required|numeric",
            "furnish" => "required|in:none,full,semi",
            "min_price" => "required|numeric",
            "max_price" => "required|numeric"
        );
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeFailResponse("Details creation failed due to validation errors.", $validation->messages()->getMessages());
        } else {
            $details = new CommonDetails();
            foreach ($this->fields as $field) {
                if (Input::has($field)) {
                    $details->$field = Input::get($field);
                }
            }
            $details->save();
            return $this->makeSuccessResponse("Details creation successful", $details->toArray());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        if($details = CommonDetails::find($id)) {
            return $this->makeSuccessResponse("Details (ID = $id) fetched", $details->toArray());
        }
        else {
            return $this->makeFailResponse("Details does not exist");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        if($details = CommonDetails::find($id)) {
            $details = new CommonDetails();
            foreach ($this->fields as $field) {
                if (Input::has($field)) {
                    $details->$field = Input::get($field);
                }
            }
            $details->save();
            return $this->makeSuccessResponse("Details (ID = $details->id) updated", $details->toArray());
        }
        else {
            return $this->makeFailResponse("Details does not exist");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        if($details = CommonDetails::find($id)) {
            $details->delete();
            return $this->makeSuccessResponse("Details (ID = $details->id) deleted");
        }
        else {
            return $this->makeFailResponse("Details does not exist");
        }
    }

}
