<?php

class PropertyController extends \BaseController {

    private $fillableFields;

    function __construct() {
        parent::__construct();
        $this->fillableFields = array(
            "name",
            "tagline",
            "description",
            "status",
            "transaction",
            "address_as_name",
            "address_id"
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $withs = array_merge(array('address', 'types'), Input::get("with", array()));
        $query = Property::with($withs);

        if (Input::has('unpublished')) {
            $query = $query->unpublished();
        } else if (Input::has('published')) {
            $query = $query->published();
        } else if (Input::has('overdue')) {
            $query = $query->overdue();
        }

        $limit = Input::get("limit", 1000);
        $offset = Input::get("offset", 0);

        $properties = $query->take($limit)->skip($offset)->get();
        $count = $query->count();

        $data = array(
            "properties" => $properties,
            "count" => $count
        );
        return $this->makeSuccessResponse("Property Resources fetched.", $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $rules = array(
            "name" => "max:128|required_if:address_as_name, 0",
            "tagline" => "max:256",
            "description" => "required",
            "status" => "required|in:rfo,so,ps",
            "transaction" => "required|in:sale,rent",
//            "address_as_name" => "in:1,0",
            "address" => "required|numeric|exists:address,id",
            "types" => "required|array"
        );
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeFailResponse("Property creation could not complete due to validation error(s).", $validation->messages()->getMessages());
        } else {
            $property = new Property();
            $property->name = Input::get("name");
            $property->tagline = Input::get("tagline");
            $property->description = Input::get("description");
            $property->status = Input::get("status");
            $property->transaction = Input::get("transaction");
            $property->address_as_name = Input::get("address_as_name", false);
            $property->address_id = Input::get("address");

            $property->created_by = Auth::user()->id;
            $property->save();

            $property->types()->sync(Input::get('types', array()));
            $property->save();

            return $this->makeSuccessResponse("Property creation successful", $property->toArray());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        $alias = $id;
        if (!is_numeric($id)) {
            $explode = explode("-", $alias);
            $count = count($explode);
            $alias = $explode[$count - 1];
        }

        $withs = Input::get('with', array(
            "types",
            "address",
            "creator.photo",
            "editor.photo",
            "agent.photo",
            "photo",
            "amenities.photo",
            "tags",
            "features",
            "specs",
            "details",
            "photos",
            "publisher",
            "units.photo",
            "units.details"
        ));

        if ($property = Property::with($withs)->find($alias)) {
            return $this->makeSuccessResponse("Property (ID = $id) fetched", $property->toArray());
        }
        return $this->makeFailResponse("Property (ID = $id) does not exist");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        if ($property = Property::find($id)) {
            $property->name = Input::get("name", $property->name);
            $property->tagline = Input::get("tagline", $property->tagline);
            $property->description = Input::get("description", $property->description);
            $property->status = Input::get("status", $property->status);
            $property->transaction = Input::get("transaction", $property->transaction);
            $property->address_as_name = Input::get("address_as_name", $property->address_as_name);
            $property->address_id = Input::get("address", $property->address_id);
            $property->agent_id = Input::get("agent_id", $property->agent_id);
            $property->agent_message = Input::get("agent_message", $property->agent_message);

            if (Input::has('types')) {
                $property->types()->sync(Input::get('types', array()));
            }

            $property->updated_by = Auth::user()->id;
            $property->save();
            return $this->makeSuccessResponse("Property (ID = $id) updated", $property->toArray());
        } else {
            return $this->makeFailResponse("Property (ID = $id) does not exist.");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        if ($property = Property::find($id)) {
            $property->deleted_by = Auth::id();
            $property->save();
            $property->delete();
            return $this->makeSuccessResponse("Property (ID = $id) deleted");
        } else {
            return $this->makeFailResponse("Property does not exist.");
        }
    }

    /**
     * Return reporting data for Proeprties
     *
     * 
     * @return Response
     */
    public function report() {
        $counts = array(
            "published" => Property::published()->count(),
            "unpublished" => Property::unpublished()->count(),
            "overdue" => Property::overdue()->count(),
        );
        return $this->makeSuccessResponse("Proeprty Report", $counts);
    }

    /**
     * Pulish a property
     *
     * @param int $id Property ID
     * @return Response
     */
    public function publish($id) {
        if ($property = Property::find($id)) {
            $rules = array(
                "publish_start" => "required|date",
                "publish_end" => "date"
            );
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeFailResponse("Publish property could not complete due to validation error(s).", $validation->messages()->getMessages());
            } else {
                $start = date("Y-m-d H:i:s", strtotime(Input::get('publish_start')));
                $end = null;
                if (Input::has('publish_end')) {
                    $end = date("Y-m-d H:i:s", strtotime(Input::get('publish_end')));
                }
                $property->publish_start = $start;
                $property->publish_end = $end;
                $property->published_by = Auth::id();
                $property->save();

                return $this->makeSuccessResponse("Property (ID = $id) published", $property->toArray());
            }
        } else {
            return $this->makeFailResponse("Property (ID = $id) does not exist.");
        }
    }

    /**
     * Store photo for a property
     * 
     * @return Response
     */
    public function mainPhoto($id) {
        $rules = array(
            "photo" => "required|cloudinary_photo",
            "caption" => "max:256"
        );
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeFailResponse("Photo upload of User (ID = $id) failed due to validation errors.", $validation->messages()->getMessages());
        } else {
            if ($property = Property::find($id)) {
                $data = Input::get('photo');
                $photo = PhotoManager::createCloudinary($data['public_id'], $property, Input::get('caption'), $data);

                if (!is_null($property->photo)) {
                    $property->photo->delete();
                }

                $property->photo_id = $photo->id;
                $property->updated_by = Auth::id();
                $property->save();

                return $this->makeSuccessResponse("Photo upload of Property (ID = $id) was successful", $photo->toArray());
            } else {
                return $this->makeFailResponse("Property does not exist");
            }
        }
    }

}
