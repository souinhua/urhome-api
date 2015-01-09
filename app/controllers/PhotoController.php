<?php

class PhotoController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $query = Photo::with('uploader');
        
        $limit = Input::get('limit', 1000);
        $offset = Input::get('offset', 0);
        
        $count = $query->count();
        $photos = $query->take($limit)->skip($offset)->get();
        return $this->makeSuccessResponse($photos, 200, "Photo resources fetched.", array(
            "X-Total-Count" => $count
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        return $this->makeResponse(null, 403, "Why u do this to me?!");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        if ($photo = Photo::find($id)) {
            return $this->makeResponse($photo, 200, "Photo (ID=$id) fetched.");
        } else {
            return $this->makeResponse(null, 404, "Photo resource not found.");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        if($photo = Photo::find($id)) {
            $rules = array(
                "caption" => "required|max:256"
            );
            $validation = Validator::make(Input::all(), $rules);
            if($validation->fails()) {
                return $this->makeResponse($validation->messages(), 400, "Request failed in Photo resource validation.");
            }
            else {
                $photo->caption = Input::get("caption");
                $photo->save();
                return $this->makeResponse($photo, 200, "Photo resource caption saved.");
            }
        }
        else {
            return $this->makeResponse(null, 404, "Photo resource not found.");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        if($photo = Photo::find($id)) {
            $photo->delete();
            return $this->makeResponse(null, 204, "Photo (ID = $id) deleted.");
        }
        else {
            return $this->makeResponse(null, 404, "Photo does not exist");
        }
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
            $response->header("Content-type", $mimeType);
            return $response;
        } else {
            return $this->makeResponse(null,404,"Photo (ID = $id) does not exist.");
        }
    }
}
