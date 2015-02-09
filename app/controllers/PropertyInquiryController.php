<?php

class PropertyInquiryController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store($propertyId) {
        if($property = Property::find($propertyId)) {
            $rules = array(
                "name" => "required|max:64", 
                "phone" => "required:max:64", 
                "email" => "required|email|max:64",
                "message" => "required",
                "unit_id" => "exists:unit,id|numeric"
            );
            $validation = Validator::make(Input::all(), $rules);
            if($validation->fails()) {
                return $this->makeResponse($validation->messages(), 400, "Failed in validation.");
            }
            else {
                $inquiry = new Inquiry();
                $inquiry->name = Input::get("name");
                $inquiry->phone = Input::get("phone");
                $inquiry->email = Input::get("email");
                $inquiry->message = Input::get("message");
                $inquiry->user_id = Auth::id();
                $inquiry->property = $property->id;
                
                if(Input::has("unit_id")) {
                    $inquiry->unit_id = Input::get("unit_id");
                }
                
                $inquiry->save();
                return $this->makeResponse($inquiry, 201, "Inquery resource saved.");
            }
        }
        else {
            return $this->makeResponse(null, 404, "Property resource not found.");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        //
    }

}