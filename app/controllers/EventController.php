<?php

/**
 * Description of EventController
 *
 * @author User
 */
class EventController extends BaseController {

    /**
     * Log property filter
     * 
     * @return Response
     */
    public function postFilterLog() {
        $rules = array(
            "bed" => "numeric",
            "bath" => "numeric",
            "min_price" => "numeric",
            "max_price" => "numeric",
            "previous_url" => "url"
        );

        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeResponse($validation->messages(), 400, "Resource failed in validation.");
        } else {
            $filterLogId = DB::table("filter_log")->insertGetId(array(
                "bed" => Input::get("bed", null),
                "bath" => Input::get("bath", null),
                "min_price" => Input::get("min_price", null),
                "max_price" => Input::get("max_price", null),
                "type" => implode(", ", Input::get("type", array())),
                "previous_url" => Input::get("previous_url", null),
                "user_id" => Auth::id()
            ));
            
            $filterLog = DB::table("filter_log")->where("id","=", $filterLogId)->first();
            return $this->makeResponse($filterLog, 201, "Property Filter resource logged.");
        }
    }

}
