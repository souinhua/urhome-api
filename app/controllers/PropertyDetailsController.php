<?php

class PropertyDetailsController extends \BaseController {

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
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store($propertyId) {
        if ($property = Property::find($propertyId)) {
            $rules = array(
                "bed" => "numeric",
                "bath" => "numeric",
                "parking" => "numeric",
                "area" => "numeric",
                "furnish" => "in:none,full,semi",
                "min_price" => "required|numeric",
                "max_price" => "numeric"
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
                $property->common_details_id = $details->id;
                $property->save();
                return $this->makeSuccessResponse("Details creation successful", $details->toArray());
            }
        } else {
            return $this->makeFailResponse("Property does not exist");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($propertyId, $detailsId = null) {
        if (($property = Property::find($propertyId))) {
            if ($property = Property::with('details')->find($propertyId)) {
                $details = $property->details;
                return $this->makeSuccessResponse("Property Details fetched", $details->toArray());
            } else {
                return $this->makeFailResponse("Property does not exist");
            }
        } else {
            return $this->makeFailResponse("Property does not exist.");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($propertyId, $detailsId = null) {
        if (($property = Property::find($propertyId))) {
            if ($property = Property::with('details')->find($propertyId)) {
                $details = $property->details;

                foreach ($this->fields as $field) {
                    $details->$field = Input::get($field, $details->$field);
                }
                $details->save();

                return $this->makeSuccessResponse("Property Details updated", $details->toArray());
            } else {
                return $this->makeFailResponse("Property does not exist");
            }
        } else {
            return $this->makeFailResponse("Property does not exist.");
        }
    }

}
