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

        $this->beforeFilter('auth', array('except' => ['index', 'show', 'related']));
        $this->beforeFilter('admin', array('only' => ['publish', 'unpublish']));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $withs = Input::get("with", array("address", "types"));

        $query = Property::with($withs);
        if (Auth::guest()) {
            $query = $query->published();
        }

        /*
         * =====================================================================
         *                              Filters
         * =====================================================================
         */
        if (Input::has('province') || Input::has('city')) {
            // Joins Address table for the location filter
            $query = $query->join("address", "property.address_id", "=", "address.id")->select('property.*');;
            if (Input::has('province')) {
                $query = $query->province(Input::get('province'));
            }

            if (Input::has('city')) {
                $query = $query->city(Input::get('city'));
            }
        }

        if (Input::has('type')) {
            $query = $query->type(Input::get('type', array()));
        }

        if (Input::has('min_price') || Input::has('max_price')) {
            // Joins CommonDetails table for the location filter
            $query = $query->join("common_details", "property.common_details_id", "=", "common_details.id")->select('property.*');
            if (Input::has('min_price')) {
                $query = $query->where("common_details.min_price",">=",Input::get("min_price"));
            }

            if (Input::has('max_price')) {
                $query = $query->where("common_details.max_price","<=",Input::get("max_price"));
            }
            $query = $query->select('property.*');
        }
        
        if(Input::has("bed") || Input::has("bath")) {
            $query = $query
                    ->join("unit","property.id","=","unit.property_id")
                    ->join("common_details","common_details.id","=","unit.common_details_id");
            
            if(Input::has("bed")) {
                $bed = Input::get("bed");
                if($bed >= 3) {
                    $query = $query->where("common_details.bed",">=", $bed);
                }
                else {
                    $query = $query->where("common_details.bed","=", $bed);
                }
            }
            
            if(Input::has("bath")) {
                $bath = Input::get("bath");
                if($bath >= 3) {
                    $query = $query->where("common_details.bath",">=", $bath);
                }
                else {
                    $query = $query->where("common_details.bath","=", $bath);
                }
            }
            $query = $query->select('property.*');
        }

        /*
         * =====================================================================
         *                              Pagination
         * =====================================================================
         */

        $limit = Input::get("limit", 1000);
        $offset = Input::get("offset", 0);

        $count = $query->count();
        $properties = $query->take($limit)->skip($offset)->get();

        return $this->makeResponse($properties, 200, "Property resources fetched.", array(
                    "X-Total-Count" => $count,
                    "X-SQL" => $query->toSql()
        ));
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
            "types" => "required|array"
        );
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return $this->makeResponse($validation->messages(), 400, "Request failed in Property resource validation.");
        } else {
            $property = new Property();
            $property->name = Input::get("name");
            $property->tagline = Input::get("tagline");
            $property->description = Input::get("description");
            $property->status = Input::get("status");
            $property->transaction = Input::get("transaction");
            $property->address_as_name = Input::get("address_as_name", false);

            $property->created_by = Auth::user()->id;
            $property->save();

            // Link Types
            $property->types()->sync(Input::get('types', array()));
            $property->save();

            return $this->makeResponse($property, 201, "Property resource created.");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
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

        if (is_numeric($id)) {
            $property = Property::with($withs)->remember(15)->find($id);
        } else {
            $property = Property::with($withs)->remember(15)->where('slug', '=', $id)->first();
        }

        if (!is_null($property)) {
            return $this->makeResponse($property, 200, "Property resource fetched.");
        }
        return $this->makeResponse(null, 404, "Property resource not found.");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        if ($property = Property::find($id)) {
            $rules = array(
                "name" => "max:128|required_if:address_as_name, 0",
                "tagline" => "max:256",
                "status" => "in:rfo,so,ps",
                "transaction" => "in:sale,rent",
                "types" => "array",
                "agent_id" => "exists:user,id",
                "developer_id" => "exists:developer,id"
            );

            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeResponse($validation->messages(), 400, "Request failed in Property resource validation.");
            } else {
                $fields = array("name", "tagline", "address_as_name", "description", "status", "transaction", "agent_id", "agent_message", "developer_id");
                foreach ($fields as $field) {
                    if ($this->hasInput($field)) {
                        $property->$field = Input::get($field);
                    }
                }

                if (Input::has('types')) {
                    $property->types()->sync(Input::get('types', array()));
                }

                $property->updated_by = Auth::user()->id;

                // Slugging
                if (!is_null($property->address)) {
                    $address = $property->address;
                    if ($property->address_as_name) {
                        $property->slug = Str::slug("$address->address-$address->city-$property->id");
                    } else {
                        $property->slug = Str::slug("$property->name-$address->city-$property->id");
                    }
                }

                $property->save();
                return $this->makeResponse($property, 200, "Property (ID = $id) resource updated.");
            }
        } else {
            return $this->makeResponse(null, 404, "Property resource not found.");
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
            return $this->makeResponse(null, 204, "Property (ID = $id) resource deleted.");
        } else {
            return $this->makeResponse(null, 404, "Property resource not found.");
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
        return $this->makeResponse($counts, 200, "Proeprty Report");
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
                return $this->makeResponse($validation->messages(), 400, "Request failed in Property publication resource validation.");
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

                return $this->makeResponse($property, 200, "Property (ID=$id) resource published.");
            }
        } else {
            return $this->makeResponse(null, 404, "Property resource not found.");
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
            return $this->makeResponse($validation->messages(), 400, "Request failed in Property Photo resource validation.");
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

                return $this->makeResponse($photo, 201, "Property Photo resource saved.");
            } else {
                return $this->makeResponse(null, 404, "Property resource not found.");
            }
        }
    }

    /**
     * Updates or Create Details resource for a Property resource.
     * 
     * @param int $propertyId
     * @param int $unitId
     * @return API Response
     */
    public function details($propertyId) {
        if ($property = Property::find($propertyId)) {

            $rules = array(
                "bed" => "numeric",
                "bath" => "numeric",
                "parking" => "numeric",
                "area" => "numeric",
                "furnish" => "in:full,semi,none",
                "min_price" => "numeric",
                "max_price" => "numeric",
                "min_bed" => "numeric",
                "max_bed" => "numeric",
                "min_bath" => "numeric",
                "max_bath" => "numeric",
                "min_area" => "numeric",
                "max_area" => "numeric",
            );

            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return $this->makeResponse($validation->messages(), 400, "Request failed in Property Unit resource validation.");
            } else {
                $details = $property->details;
                if (is_null($details)) {
                    $details = new CommonDetails();
                }

                foreach ($rules as $field => $rule) {
                    if ($this->hasInput($field)) {
                        $details->$field = Input::get($field);
                    }
                }
                $details->save();
                $property->common_details_id = $details->id;

                $property->save();
                return $this->makeResponse($details, 200, "Property Unit resource saved.");
            }
        } else {
            return $this->makeResponse(null, 404, "Property Unit resource not found.");
        }
    }

    /**
     * Stores or Updates a Property Address resource
     * 
     * @param int $id
     * @return Response
     */
    public function address($id) {
        if ($property = Property::find($id)) {
            $address = $property->address;
            if (is_null($address)) {
                $address = new Address();
            }

            $fields = array("address", "street", "city", "province", "zip", "lng", "lat", "zoom", "accessibility");
            foreach ($fields as $field) {
                if ($this->hasInput($field)) {
                    $address->$field = Input::get($field);
                }
            }
            $address->save();

            $property->address_id = $address->id;

            // Slugging
            if ($property->address_as_name) {
                $property->slug = Str::slug("$address->address-$address->city-$property->id");
            } else {
                $property->slug = Str::slug("$property->name-$address->city-$property->id");
            }

            $property->save();

            return $this->makeResponse($address, 200, "Property Address resource saved.");
        } else {
            return $this->makeResponse(null, 404, "Property resource not found.");
        }
    }

    /**
     * Fetches related Property resources
     * 
     * @param int $id
     * @return Property Collection resource
     */
    public function related($id) {
        if ($property = Property::find($id)) {
            $city = $property->address->city;
            $types = array();
            foreach ($property->types as $type) {
                $types[] = $type->id;
            }

            $data = DB::select(""
                            . "SELECT DISTINCT "
                            . " p.id "
                            . "FROM property p "
                            . " INNER JOIN address a ON a.id = p.address_id "
                            . " INNER JOIN property_type pt ON pt.property_id = p.id "
                            . "WHERE "
                            . " a.city = ? "
                            . " AND pt.type_id IN(?) "
                            . " AND p.id != ?", array($city, implode(",", $types), $property->id));

            $ids = array();
            foreach ($data as $pId) {
                $ids[] = $pId->id;
            }

            if (isset($ids[0])) {
                $with = Input::get("with", ["address", "types"]);
                $query = Property::with($with)->whereIn('id', $ids);

                $limit = Input::get("limit", 1000);
                $offset = Input::get("offset", 0);

                $count = $query->count();
                $properties = $query->take($limit)->skip($offset)->get();
            } else {
                $properties = array();
                $count = 0;
            }

            return $this->makeResponse($properties, 200, "Property resources related to Property (ID = $id) fetched.", array(
                        "X-Total-Count" => $count
            ));
        } else {
            return $this->makeResponse(null, 404, "Property resource not found.");
        }
    }

}
