<?php

/**
 * Description of UnitTagController
 *
 * @author J
 */
class UnitTagController extends BaseController {
    
    /**
     * 
     * @param int $unitId
     * @return Response
     */
    public function index($unitId) {
        if ($unit = Unit::find($unitId)) {
            $tags = $unit->tags;
            return $this->makeResponse($tags, 200, "Unit Tag resources fetched");
        } else {
            return $this->makeResponse(null, 404, "Unit Tag resource not found.");
        }
    }
    
    /**
     * Fetches a Unit Tag resource
     * 
     * @param int $unitId
     * @param int $tagId
     * @return Response
     */
    public function show($unitId, $tagId) {
        if($tag = Unit::find($unitId)->tags()->find($tagId)) {
            return $this->makeResponse($tag, 200, "Unit Tag (ID = $tagId) fetched.");
        }
        else {
            return $this->makeResponse(null, 404, "Unit Tag resource not found.");
        }
    }

    /**
     * Stores the Tag resource of a Unit
     * 
     * @param int $unitId
     * @return Tag
     */
    public function store($unitId) {
        if ($unit = Unit::find($unitId)) {
            $rules = array(
                "name" => "required"
            );
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeResponse($validation->messages(), 400, "Request failed in Unit Tag resource validation.");
            } else {
                if (is_array(Input::get("name"))) {
                    foreach (Input::get("name") as $name) {
                        $tag = new Tag();
                        $tag->name = $name;
                        $tag->save();

                        $unit->tags()->attach($tag->id);
                    }
                } else {
                    $tag = new Tag();
                    $tag->name = Input::get("name");
                    $tag->save();

                    $unit->tags()->attach($tag->id);
                }
                
                $unit->save();

                return $this->makeResponse($tag, 201, "Unit Tag created.");
            }
        } else {
            return $this->makeResponse(null, 404, "Unit resource not found.");
        }
    }

    /**
     * Deletes a Unit tag resource.
     * 
     * @param int $unitId
     * @param int $tagId
     * @return Response
     */
    public function destroy($unitId, $tagId) {
        if ($tag = Unit::find($unitId)->tags()->find($tagId)) {
            $tag->delete();
            return $this->makeResponse(null, 204, "Unit Tag resource deleted.");
        } else {
            return $this->makeResponse(null, 404, "Unit Tag resource not found.");
        }
    }

}
