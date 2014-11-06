<?php

class PropertyController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $withs = array_merge(array('address','types'), Input::get("with", array()));
        $query = Property::with($withs);
        
        $properties = $query->get();
        return $this->makeSuccessResponse("Property Resources fetched.", $properties->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $rules = array(
            "name" => "max:128|required_if:address_as_name, 0",
            "tagline" => "max:256",
            "description" => "required",
            "status" => "required|in:rfo,so,ps",
            "transaction" => "required|in:sale,rent",
//            "address_as_name" => "in:1,0",
            "address" => "required|numeric|exists:address,id",
            "types" => "required|array"
        );
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeFailResponse("Property creation could not complete due to validation error(s).", $validation->messages()->getMessages());
        } else {
            $property = new Property();
            $property->name = Input::get("name");
            $property->tagline = Input::get("tagline");
            $property->description = Input::get("description");
            $property->status = Input::get("status");
            $property->transaction = Input::get("transaction");
            $property->address_as_name = Input::get("address_as_name", false);
            $property->address_id = Input::get("address");
            
            $property->created_by = Auth::user()->id;
            $property->save();
            
            $property->types()->sync(Input::get('types', array()));
            $property->save();

            return $this->makeSuccessResponse("Property creation successful", $property->toArray());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        $withs = array_merge(array('address','types'), Input::get("with", array()));
        if ($property = Property::with($withs)->find($id)) {
            return $this->makeSuccessResponse("Property (ID = $id) fetched", $property->toArray());
        }
        return $this->makeFailResponse("Property (ID = $id) does not exist");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        if ($property = Property::find($id)) {
            $property->name = Input::get("name", $property->name);
            $property->tagline = Input::get("tagline", $property->tagline);
            $property->description = Input::get("description", $property->description);
            $property->status = Input::get("status", $property->status);
            $property->transaction = Input::get("transaction", $property->transaction);
            $property->address_as_name = Input::get("address_as_name", $property->address_as_name);
            $property->address_id = Input::get("address", $property->address_id);

            if (Input::has('types')) {
                $property->types()->sync(Input::get('types', array()));
            }

            $property->updated_by = Auth::user()->id;
            $property->save();
            return $this->makeSuccessResponse("Property (ID = $id) updated", $property->toArray());
        } else {
            return $this->makeFailResponse("Property (ID = $id) does not exist.");
        }
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
    
    /**
     * Store photo for a property
     * 
     * @return Response
     */
    public function postPhoto($id) {
        $rules = array(
            "photo" => "required|image"
        );
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeFailResponse("Photo upload of User (ID = $id) failed due to validation errors.", $validation->messages()->getMessages());
        } else {
            if ($property = Property::find($id)) {
                if (!is_null($property->photo)) {
                    $property->photo->delete();
                }

                $extension = Input::file('photo')->getClientOriginalExtension();
                $fileName = $property->id . '_' . Auth::user()->id .'_'. time() . "." . $extension;

                $propertyDir = public_path() . "/uploads/properties/$property->id";
                if(!File::isDirectory($propertyDir)) {
                    File::makeDirectory($propertyDir, 0775, true);
                }
                
                $destinationPath = public_path() . "/uploads/properties/$property->id";
                Input::file('photo')->move($destinationPath, $fileName);

                $photo = new Photo();
                $photo->path = "$destinationPath/$fileName";
                $photo->url = URL::to("/uploads/properties/$property->id/$fileName");
                $photo->uploaded_by = Auth::user()->id;
                $photo->save();

                if(!is_null($property->photo)) {
                    $property->photo->delete();
                }
                $property->photo_id = $photo->id;
                $property->save();

                return $this->makeSuccessResponse("Photo upload of Property (ID = $id) was successful", $photo->toArray());
            } else {
                return $this->makeFailResponse("Property does not exist");
            }
        }
    }

}
