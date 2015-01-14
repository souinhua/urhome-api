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

        return $this->makeResponse($types, 200, "Type resources fetched.");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        $type = Type::find($id);
        return $this->makeResponse($type, 200, "Type resource fetched.");
    }
}
