<?php

class PhotoController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        print_r(Input::all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        
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

    /**
     * Display the specified Photo resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function display($id) {
        if ($photo = Photo::find($id)) {
            
            $file = new \Symfony\Component\HttpFoundation\File\File($photo->path);
            $mimeType = $file->getMimeType();

            $blob = File::get($photo->path);
            $response = Response::make($blob, 200);
            $response->header("Content-type",$mimeType);
            return $response;
        } else {
            return $this->makeFailResponse("Photo (ID = $id) does not exist.");
        }
    }

}
