<?php

class ContentController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        /**
         * ---------------------------------------------------------------------
         * Relationships
         * ---------------------------------------------------------------------
         */
        $with = Input::get('with', array('photo', 'created_by'));
        $query = Content::with($with);

        /**
         * ---------------------------------------------------------------------
         * Filters
         * ---------------------------------------------------------------------
         */
        if (Input::has("type")) {
            $query->ofType(Input::get("type"));
        }
        
        if(Input::get('published', false)) {
            $query->published();
        }
        
        /**
         * ---------------------------------------------------------------------
         * Ordering
         * ---------------------------------------------------------------------
         */
        $query->orderBy('publish_start','desc');

        /**
         * ---------------------------------------------------------------------
         * Pagination
         * ---------------------------------------------------------------------
         */
        $limit = Input::get("limit", 1000);
        $offset = Input::get("offset", 0);

        /**
         * ---------------------------------------------------------------------
         * Query Execution
         * ---------------------------------------------------------------------
         */
        $count = $query->count();
        $contents = $query->take($limit)->skip($offset)->get();

        return $this->makeResponse($contents, 200, "Content resources fetched.", array(
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
            "title" => "required|max:128",
            "abstract" => "max:1024",
            "body" => "required",
            "type" => "required|in:article,event,news,ad,testimonial,video,photo,list",
        );

        if (Input::get("type") == "event") {
            $rules["event_start"] = "required|date";
            $rules["event_end"] = "date";
        }

        if (Input::get("type") == "video") {
            $rules["embed"] = "required";
        }

        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeResponse($validation->messages(), 400, "Request failed in Content resource validation.");
        } else {
            $content = new Content();

            $content->title = Input::get("title");
            $content->type = Input::get("type");
            $content->abstract = Input::get("abstract");
            $content->body = Input::get("body");

            if (Input::get("type") == "event") {
                $content->event_start = Input::get("event_start");
                $content->event_end = Input::get("event_end");
            }

            if (Input::get("type") == "video") {
                $content->embed = Input::get("embed");
            }

            $content->created_by_id = Auth::id();
            $content->save();

            $content->slug = Str::slug("$content->title-$content->id");
            $content->save();

            return $this->makeResponse($content, 201, "Content resource created.");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        $with = Input::get('with', array('photo', 'created_by'));

        if (is_numeric($id)) {
            $content = Content::with($with)->find($id);
        } else {
            $content = Content::with($with)->where("slug", "=", $id)->first();
        }

        if (!is_null($content)) {
            return $this->makeResponse($content, 200, "Content resource (ID = $id) fetched.");
        } else {
            return $this->makeResponse(null, 404, "Content resource not found.");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        if ($content = Content::find($id)) {

            $fields = array("title", "abstract", "body", "type");

            foreach ($fields as $field) {
                if (Input::has($field)) {
                    $content->$field = Input::get($field);
                }
            }

            if (Input::has("title")) {
                $content->slug = Str::slug("$content->title-$content->id");
            }

            $content->updated_by_id = Auth::id();
            $content->save();
            return $this->makeResponse($content, 200, "Content resource (ID = $id) updated.");
        } else {
            return $this->makeResponse(null, 404, "Content resource not found.");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        if ($content = Content::find($id)) {
            $content->deleted_by = Auth::id();
            $content->save();
            $content->delete();
            return $this->makeResponse(null, 204, "Content (ID = $id) deleted");
        } else {
            return $this->makeResponse(null, 404, "Content resource not found.");
        }
    }

    /**
     * Pulish a property
     *
     * @param int $id Property ID
     * @return Response
     */
    public function publish($id) {
        if ($content = Content::find($id)) {
            $rules = array(
                "publish_start" => "required|date"
            );
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeFailResponse("Publihs property could not complete due to validation error(s).", $validation->messages()->getMessages());
            } else {
                $start = date("Y-m-d H:i:s", strtotime(Input::get('publish_start')));
                $end = null;
                if (Input::has('publish_end')) {
                    $end = date("Y-m-d H:i:s", strtotime(Input::get('publish_end')));
                }
                $content->publish_start = $start;
                $content->publish_end = $end;
                $content->publish_by_id = Auth::id();
                $content->save();

                return $this->makeSuccessResponse($content, 200, "Content resource (ID = $id) published.");
            }
        } else {
            return $this->makeFailResponse(null, 404, "Content resource (ID = $id) does not exist.");
        }
    }

    /**
     * Unpublish Property
     * 
     * @param int $id Property ID
     * 
     * @return Response
     */
    public function unpublish($id) {
        if ($content = Content::find($id)) {
            $content->publish_start = null;
            $content->publish_end = null;
            $content->publish_by_id = null;
            $content->save();

            return $this->makeSuccessResponse($content, 200, "Content resource (ID = $id) unpublished.");
        } else {
            return $this->makeFailResponse(null, 404, "Content resource (ID = $id) does not exist.");
        }
    }

    /**
     * Stores Cloudinary Photo resource for Content.
     * 
     * @param type $contentId
     * @return Response
     */
    public function mainPhoto($contentId) {
        if ($content = Content::find($contentId)) {
            $rules = array(
                "photo" => "required|cloudinary_photo",
                "caption" => "max:256"
            );

            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeResponse($validation->messages(), 400, "Request failed in Content Photo validation.");
            } else {
                $data = Input::get('photo');
                $photo = PhotoManager::createCloudinary($data['public_id'], $content, Input::get('caption'), $data);

                $content->photo_id = $photo->id;
                $content->updated_by_id = Auth::id();
                $content->save();

                return $this->makeResponse($photo, 201, "Cloudainry photo resource created.");
            }
        } else {
            return $this->makeResponse(null, 404, "Content resource not found.");
        }
    }

}
