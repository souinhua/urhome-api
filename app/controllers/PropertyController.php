<?php

class PropertyController extends \BaseController {

    private $fillableFields;

    function __construct() {
        parent::__construct();
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
         * ---------------------------------------------------------------------
         * Property Filters and Search
         * ---------------------------------------------------------------------
         */
        if (Input::has('place')) {
            // Joins Address table for the location filter
            $query = $query->join("address", "property.address_id", "=", "address.id")->select('property.*');
            $query = $query->where(function($query) {
                $place = DB::getPdo()->quote(Input::get('place'));
                $likeValue = '%' . Input::get('place') . '%';
                $quotedLike = DB::getPdo()->quote($likeValue);
                $query
                        ->whereRaw("lower(replace(concat(address.city,' ',address.province),' ','-')) = ?", array($place))
                        ->orWhere("address.city", "LIKE", $likeValue)
                        ->orWhere("address.province", "LIKE", $likeValue)
                        ->orWhere("address.address", "LIKE", $likeValue)
                        ->orWhere("address.zip", "=", Input::get('place'))
                        ->orWhereRaw("concat(address.address,', ', address.city,', ', address.province,' ', address.zip) LIKE $quotedLike");
            });
        }

        if (Input::has('type')) {
            $query = $query->type(Input::get('type', array()));
        }

        if (Input::has("bed") || Input::has("bath") || Input::has("min_price") || Input::has("max_price")) {
            $query = $query->whereIn("property.id", function($query) {
                $query
                        ->selectRaw("if(property_id is null, id, property_id) id")
                        ->from("property");

                if (Input::has("bed")) {
                    $bed = Input::get("bed");
                    if($bed == 3) {
                        $query->where("bed", ">=", $bed);
                    }
                    else {
                        $query->where("bed", "=", $bed);
                    }
                    
                }

                if (Input::has("bath")) {
                    $query->where("bath", "=", Input::get("bath"));
                }

                if (Input::has("min_price")) {
                    $query->where(function($query) {
                        $query
                                ->where("min_price","<=",Input::get("min_price"))
                                ->where("max_price",">=",Input::get("min_price"));
                    });
                }
                
                if (Input::has("max_price")) {
                    $query->where(function($query) {
                        $query
                                ->where("min_price","<=",Input::get("max_price"))
                                ->where("max_price",">=",Input::get("max_price"));
                    });
                }
            });
        }

        $query = $query->whereNull('property_id');

        /*
         * ---------------------------------------------------------------------
         * Pagination
         * ---------------------------------------------------------------------
         * 
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
        $fields = array(
            "name" => "required_without:address_as_name|max:128",
            "tagline" => "max:128",
            "address_as_name" => "in:0,1",
            "description" => "max:1024",
            "status" => "required|in:rfo,so,ps",
            "transaction" => "required|in:sale,rent",
            "agent_id" => "required|exists:user,id",
            "address_id" => "exists:address,id",
            "developer_id" => "exists:developer,id",
            "agent_message" => "max:1024",
            "bed" => "numeric",
            "bath" => "numeric",
            "area" => "numeric",
            "min_price" => "numeric",
            "max_price" => "numeric",
            "furnish" => "required|in:semi,full,none",
            "parking" => "numeric",
            "quantity" => "numeric",
            "property_id" => "exists:property,id",
            "types" => "required|array"
        );

        $validation = Validator::make(Input::all(), $fields);
        if ($validation->fails()) {
            return $this->makeResponse($validation->messages(), 409, "Validation failed.");
        } else {

            $property = new Property();
            unset($fields['types']);
            foreach ($fields as $field => $rules) {
                if (Input::has($field)) {
                    $property->$field = Input::get($field);
                }
            }

            $property->created_by_id = Auth::id();
            $property->updated_by_id = Auth::id();

            $property->save();
            if (Input::has("types")) {
                $property->types()->sync(Input::get("types"));
            }
            $this->generateSlug($property->id);
            return $this->makeResponse($property, 201, "Property Resource created.");
        }
    }

    /**
     * Generates a unique slug of this Property resource
     * 
     * @param int $propertyId
     * @return boolean|slug
     */
    private function generateSlug($propertyId) {
        $property = Property::find($propertyId);
        $address = $property->address;
        if (!is_null($address)) {
            if ($property->address_as_name) {
                $name = "$address->address-$address->city-$property->id";
            } else {
                $name = "$property->name-$address->city-$property->id";
            }
            $slug = Str::slug($name);
            $property->slug = $slug;
            $property->save();

            return $slug;
        } else {
            return false;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        $withs = Input::get('with', array("types", "address"));

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
            $fields = array(
                "name" => "max:128",
                "tagline" => "max:128",
                "address_as_name" => "in:0,1",
                "description" => "max:1024",
                "status" => "in:rfo,so,ps",
                "transaction" => "in:sale,rent",
                "agent_id" => "exists:user,id",
                "address_id" => "exists:address,id",
                "developer_id" => "exists:developer,id",
                "agent_message" => "max:1024",
                "bed" => "numeric",
                "bath" => "numeric",
                "area" => "numeric",
                "min_price" => "numeric",
                "max_price" => "numeric",
                "furnish" => "in:semi,full,none",
                "parking" => "numeric",
                "quantity" => "numeric",
                "property_id" => "exists:property,id",
                "types" => "array"
            );

            $validation = Validator::make(Input::all(), $fields);
            if ($validation->fails()) {
                return $this->makeResponse($validation->messages(), 409, "Validation failed.");
            } else {
                unset($fields['types']);
                foreach ($fields as $field => $rules) {
                    if ($this->hasInput($field)) {
                        $property->$field = Input::get($field);
                    }
                }

                if (Input::has("types")) {
                    $property->types()->sync(Input::get("types"));
                }

                $property->updated_by_id = Auth::id();
                $property->save();

                $this->generateSlug($property->id);
                return $this->makeResponse($property, 200, "Property Resource updated.");
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
            $property->deleted_by_id = Auth::id();
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
                $property->published_by_id = Auth::id();
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

                if (!is_null($property->mainPhoto)) {
                    $property->mainPhoto->delete();
                }

                $property->main_photo_id = $photo->id;
                $property->updated_by_id = Auth::id();
                $property->save();

                return $this->makeResponse($photo, 201, "Property Photo resource saved.");
            } else {
                return $this->makeResponse(null, 404, "Property resource not found.");
            }
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
            $property->updated_by_id = Auth::id();
            $property->save();

            $this->generateSlug($property->id);
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
