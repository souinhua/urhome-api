<?php

class DeveloperController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $query = Developer::with('photo');

        $limit = Input::get("limit", 1000);
        $offset = Input::get("offset", 0);

        $count = $query->count();
        $developers = $query->take($limit)->skip($offset)->get();

        $message = "Developers fetched";
        return $this->makeResponse($developers, 200, $message, array(
                    "X-Total-Count" => $count
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $rules = array(
            "name" => "required|max:64"
        );
        $validation = Validator::make(Input::all(), $rules);
        if($validation->fails()) {
            return $this->makeResponse($validation->messages(), 400, "Request failed in validation");
        }
        else {
            $developer = new Developer();
            $developer->name = Input::get("name");
            $developer->save();
            
            return $this->makeResponse($developer, 201, "Developer created");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        if ($developer = Developer::find($id)) {
            $message = "Developer (ID = $id) fetched";
            return $this->makeResponse($developer, 200, $message);
        } else {
            return $this->makeResponse(null, 400, "Developer does not exist");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        $rules = array(
            "name" => "required|max:64"
        );
        $validation = Validator::make(Input::all(), $rules);
        if($validation->fails()) {
            return $this->makeResponse($validation->messages(), 400, "Request failed in validation");
        }
        else {
            $developer = Developer::find($id);
            $developer->name = Input::get("name");
            $developer->save();
            
            return $this->makeResponse($developer, 200, "Developer updated.");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        if($developer = Developer::find($id)) {
            $developer->delete();
            return $this->makeResponse(null, 200, "Developer (ID=$id) deleted.");
        }
        else {
            return $this->makeResponse(null, 400, "Developer does not exist.");
        }
    }

}
