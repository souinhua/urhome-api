<?php

/**
 * Description of PropertyTagController
 *
 * @author Janssen Canturias
 */
class PropertyTagController extends BaseController {

    /**
     * 
     * @param int $propertyId
     * @return Response
     */
    public function index($propertyId) {
        if ($property = Property::find($propertyId)) {
            $tags = $property->tags;
            return $this->makeResponse($tags, 200, "Property Tag resources fetched");
        } else {
            return $this->makeResponse(null, 404, "Property Tag resource not found.");
        }
    }
    
    /**
     * Fetches a Property Tag resource
     * 
     * @param int $propertyId
     * @param int $tagId
     * @return Response
     */
    public function show($propertyId, $tagId) {
        if($tag = Property::find($propertyId)->tags()->find($tagId)) {
            return $this->makeResponse($tag, 200, "Property Tag (ID = $tagId) fetched.");
        }
        else {
            return $this->makeResponse(null, 404, "Property Tag resource not found.");
        }
    }

    /**
     * Stores the Tag resource of a Property
     * 
     * @param int $propertyId
     * @return Tag
     */
    public function store($propertyId) {
        if ($property = Property::find($propertyId)) {
            $rules = array(
                "name" => "required"
            );
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeResponse($validation->messages(), 400, "Request failed in Property Tag resource validation.");
            } else {
                if (is_array(Input::get("name"))) {
                    foreach (Input::get("name") as $name) {
                        $tag = new Tag();
                        $tag->name = $name;
                        $tag->save();

                        $property->tags()->attach($tag->id);
                    }
                } else {
                    $tag = new Tag();
                    $tag->name = Input::get("name");
                    $tag->save();

                    $property->tags()->attach($tag->id);
                }
                $property->updated_by = Auth::id();
                $property->save();

                return $this->makeResponse($tag, 201, "Property Tag created.");
            }
        } else {
            return $this->makeResponse(null, 404, "Property resource not found.");
        }
    }

    /**
     * Deletes a Property tag resource.
     * 
     * @param int $propertyId
     * @param int $tagId
     * @return Response
     */
    public function destroy($propertyId, $tagId) {
        if ($tag = Property::find($propertyId)->tags()->find($tagId)) {
            $tag->delete();
            return $this->makeResponse(null, 204, "Property Tag resource deleted.");
        } else {
            return $this->makeResponse(null, 404, "Property Tag resource not found.");
        }
    }

}
