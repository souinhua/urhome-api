<?php

class Property extends Eloquent {

    use SoftDeletingTrait;

    protected $table = "property";
    protected $appends = array("published", "overdue", "unpublished", "status_name", "photos_count", "address_name");

    /*
     * Property Scopes
     */

    public function scopeUnpublished($query) {
        return $query->whereNull('publish_start')->whereNull('publish_end');
    }

    public function scopePublished($query) {
        $dateStr = date("Y-m-d H:i:s", time());
        $date = DB::getPdo()->quote($dateStr);
        return $query->whereRaw("publish_start IS NOT NULL AND if(publish_end IS NOT NULL, publish_end > ?, TRUE)", array($date));
    }

    public function scopeOverdue($query) {
        $dateStr = date("Y-m-d H:i:s", time());
        $date = DB::getPdo()->quote($dateStr);
        return $query->whereRaw("publish_start IS NOT NULL AND if(publish_end IS NOT NULL, publish_end < ?, TRUE)", array($date));
    }

    public function scopeType($query, array $type) {
        return $query
                        ->join('property_type', 'property.id', '=', 'property_type.property_id')
                        ->join('type', 'property_type.type_id', '=', 'type.id')
                        ->where(function($query) use ($type) {
                            $query->whereIn('type.id', $type)
                            ->orWhereIn('type.slug', $type);
                        })->select('property.*');
    }

    /*
     * Property Relationships
     */

    public function types() {
        return $this->belongsToMany('Type', 'property_type', 'property_id', 'type_id');
    }

    public function address() {
        return $this->hasOne('Address', 'id', 'address_id');
    }

    public function agent() {
        return $this->hasOne('User', 'id', 'agent_id');
    }

    public function mainPhoto() {
        return $this->hasOne('Photo', 'id', 'main_photo_id');
    }

    public function amenities() {
        return $this->hasMany('Amenity', 'property_id');
    }

    public function tags() {
        return $this->belongsToMany('Tag', 'property_tag', 'property_id', 'tag_id');
    }

    public function features() {
        return $this->hasMany('Feature', 'property_id');
    }

    public function specs() {
        return $this->hasMany('Spec', 'property_id');
    }

    public function photos() {
        return $this->belongsToMany('Photo', 'property_photo', 'property_id', 'photo_id');
    }
    /*
     * Custom Attributes
     */

    public function getPublishedAttribute() {
        return !is_null($this->publish_start) && !$this->getOverdueAttribute();
    }

    public function getUnpublishedAttribute() {
        return is_null($this->publish_start);
    }

    public function getOverdueAttribute() {
        $publishEnd = strtotime($this->publish_end);
        $time = time();

        return $publishEnd < $time && !is_null($this->publish_end);
    }

    public function getStatusNameAttribute() {
        if ($this->status == 'rfo') {
            $statusName = "Ready For Occupancy";
        } else if ($this->status == 'ps') {
            $statusName = "Preselling";
        } else if ($this->status == 'so') {
            $statusName = "Sold/Sold Out";
        } else {
            $statusName = "Unavailable";
        }
        return $statusName;
    }

    public function getPhotosCountAttribute() {
        $count = 0;
        if (!is_null($this->photo)) {
            $count++;
        }
        $count += $this->photos->count();
        return $count;
    }

    public function getAddressNameAttribute() {
        if ($this->address_as_name) {
            $address = $this->address;
            $name = "$address->address, $address->city $address->zip";
            return $name;
        } else {
            return $this->name;
        }
    }
}
