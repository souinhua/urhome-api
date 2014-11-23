<?php

class UnitController extends \BaseController {

    private $detailsFields;

    function __construct() {
        $this->detailsFields = array(
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
        $units = Unit::with('property')->get();
        return $this->makeSuccessResponse("Units fetched", $units->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $rules = array(
            "name" => "required|max:64",
            "property_id" => "required|numeric|exists:property,id",
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
            return $this->makeFailResponse("Unit creation failed due to validation error(s).", $validation->messages()->getMessages());
        } else {
            $unit = new Unit();
            $unit->name = Input::get("name");
            $unit->description = Input::get("description");

            $details = new CommonDetails();
            foreach ($this->detailsFields as $field) {
                if (Input::has($field)) {
                    $details->$field = Input::get($field);
                }
            }
            $details->save();
            $unit->common_details_id = $details->id;
            $unit->property_id = Input::get('property_id');
            $unit->save();


            return $this->makeSuccessResponse("Unit created", $unit->toArray());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        if ($unit = Unit::with('property', 'photo', 'details')->find($id)) {
            return $this->makeSuccessResponse("Unit (ID = $id) fetched.", $unit->toArray());
        } else {
            return $this->makeFailResponse("Unit does not exist");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        if ($unit = Unit::with('property', 'details', 'photo')->find($id)) {
            $unit->name = Input::get("name" . $unit->name);
            $unit->description = Input::get("description", $unit->description);

            $details = $unit->details;
            foreach ($this->detailsFields as $field) {
                if (Input::has($field)) {
                    $details->$field = Input::get($field);
                }
            }
            $details->save();
            $unit->save();

            return $this->makeSuccessResponse("Unit (ID = $id) updated.", $unit->toArray());
        } else {
            return $this->makeFailResponse("Unit does not exist");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        if ($unit = Unit::with('property', 'details', 'photo')->find($id)) {

            $unit->photo->delete();
            $unit->details->delete();

            $dir = public_path("uploads/properties/$unit->property_id/units/$unit->id");
            if(File::isDirectory($dir)) {
                File::deleteDirectory($dir);
            }
            $unit->delete();

            return $this->makeSuccessResponse("Unit (ID = $id) deleted.", $unit->toArray());
        } else {
            return $this->makeFailResponse("Unit does not exist");
        }
    }
    
    public function postPhoto($id) {
        if($unit = Unit::find($id)) {
            $rules = array(
                "photo" => "required|image", 
                "caption" => "max:256"
            );
            $validation = Validator::make(Input::all(), $rules);
            if($validation->fails()) {
                return $this->makeFailResponse("Photo upload failed due to validation error(s).", $validation->messages()->getMessages());
            }
            else {
                $uploadPath = "uploads/properties/$unit->property_id/units/$unit->id";
                $dir = public_path($uploadPath);
                if(!File::isDirectory($dir)) {
                    File::makeDirectory($dir);
                }
                $file = Input::file('photo');
                $extension = $file->getClientOriginalExtension();
                $fileName = $unit->id . '_' . Auth::id() . '_' . time() . '.' . $extension;
                
                $fullPath = "$dir/$fileName";
                $file->move($dir, $fileName);
                
                $photo = new Photo();
                $photo->path = $fullPath;
                $photo->url = URL::to("$uploadPath/$fileName");
                $photo->uploaded_by = Auth::id();
                $photo->caption = Input::get("caption",null);
                $photo->save();
                
                $unit->photos()->attach($photo->id);
                
                return $this->makeSuccessResponse("Upload successful", $photo->toArray());
            }
        }
        else {
            return $this->makeFailResponse("Unit does not exist");
        }
    }

}
