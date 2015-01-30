<?php

/**
 * Description of UnitPhotoController
 *
 * @author user
 */
class UnitPhotoController extends BaseController {

    /**
     * Fetches Photo of Unit resource
     * 
     * @param int $unitId
     * @return Response
     */
    public function index($unitId) {
        if ($unit = Unit::find($unitId)) {
            $photos = $unit->photos;
            return $this->makeResponse($photos, 200, "Unit Photo resource fetched.");
        } else {
            return $this->makeResponse(null, 404, "Unit resource not found.");
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param int $unitId
     * @return Response
     */
    public function store($unitId) {
        $rules = array(
            "photo" => "required|cloudinary_photo",
            "caption" => "max:256"
        );

        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeResponse($validation->messages(), 400, "Request failed in Unit Photo resource validation.");
        } else {
            if ($unit = Unit::find($unitId)) {
                $data = Input::get('photo');
                $photo = PhotoManager::createCloudinary($data['public_id'], $unit, Input::get('caption'), $data);

                $unit->photos()->attach($photo->id);
                $unit->save();

                return $this->makeResponse($photo, 201, "Unit Photo resource created.");
            } else {
                return $this->makeResponse(null, 404, "Unit resource not found.");
            }
        }
    }
    
    /**
     * Deletes a Unit Photo resource
     * 
     * @param int $unitId
     * @param int $photoId
     * @return Response
     */
    public function destroy($unitId, $photoId) {
        if($photo = Unit::find($unitId)->photos()->find($photoId)) {
            $photo->delete();
            return $this->makeResponse(null, 204, "Unit Photo (ID = $photoId) deleted.");
        }
        else {
            return $this->makeResponse(null, 404, "Unit Photo resource not found.");
        }
    }
 
}
