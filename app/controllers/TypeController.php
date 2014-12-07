<?php

class TypeController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {

        $types = Cache::remember('types', 1440, function() {
                    return Type::all();
                });

        return $this->makeSuccessResponse("Types resource fetched", $types->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $rules = array(
            "name" => "required|max:32", 
            "description" => "required",
            "parent_id" => "exists:type,id"
        );
        $validation = Validator::make(Input::all(), $rules);
        if($validation->fails()) {
            return $this->makeFailResponse("Type creation failed due to validation error(s).", $validation->messages()->getMessages());
        }
        else {
            $type = new Type();
            $type->name = Input::get("name");
            $type->description = Input::get("description");
            $type->parent_id = Input::get("parent_id", null);
            $type->save();
            
            return $this->makeSuccessResponse("Type resource created", $type->toArray());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        $type = Cache::remember("types-$id", 1440, function($id) {
                    return Type::find($id);
                });

        return $this->makeSuccessResponse("Types (ID = $id) resource fetched", $type->toArray());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        if($type = Type::find($id)) {
            foreach(array("name", "description", "parent_id") as $field) {
                $type->$field = Input::get($field, $type->$field);
            }
            $type->save();
            return $this->makeSuccessResponse("Type resource updated", $type->toArray());
        }
        else {
            return $this->makeFailResponse("Type does not exist");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        if($type = Type::find($id)) {
            $type->delete();
            return $this->makeSuccessResponse("Type resource deleted", $type->toArray());
        }
        else {
            return $this->makeFailResponse("Type does not exist");
        }
    }

}
