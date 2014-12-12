<?php

class Property extends Eloquent {
    
    use SoftDeletingTrait;
    
    protected $table = "property";
    protected $appends = array("published","overdue","unpublished","alias","status_name","photos_count","address_name");
    
    /*
     * Property Scopes
     */
    
    public function scopeUnpublished($query) {
        return $query->where('publish_start','=',null)->where('publish_end','=',null);
    }
    
    public function scopePublished($query) {
        return $query->whereNotNull('publish_start')->where('publish_end','<', date('Y-m-d H:i:s', time()));
    }
    
    public function scopeOverdue($query) {
        return $query->where('publish_end','<', date('Y-m-d H:i:s', time()));
    }
    
    /*
     * Property Relationships
     */
    public function types() {
        return $this->belongsToMany('Type','property_type','property_id','type_id');
    }
    
    public function address() {
        return $this->hasOne('Address','id','address_id');
    }
    
    public function creator() {
        return $this->hasOne('User','id','created_by');
    }
    
    public function editor() {
        return $this->hasOne('User','id','updated_by');
    }
    
    public function agent() {
        return $this->hasOne('User','id','agent_id');
    }
    
    public function photo() {
        return $this->hasOne('Photo','id','photo_id');
    }
    
    public function amenities() {
        return $this->hasMany('Amenity','property_id');
    }
    
    public function tags() {
        return $this->belongsToMany('Tag','property_tag','property_id','tag_id');
    }
    
    public function features() {
        return $this->belongsToMany('Feature','property_feature','property_id','feature_id');
    }
    
    public function specs() {
        return $this->belongsToMany('Spec','property_spec','property_id','spec_id');
    }
    
    public function details() {
        return $this->hasOne('CommonDetails','id','common_details_id');
    }
    
    public function photos() {
        return $this->belongsToMany('Photo','property_photo','property_id','photo_id');
    }
    
    public function publisher() {
        return $this->hasOne("Person", "id", "published_by");
    }
    
    public function units() {
        return $this->hasMany("Unit","property_id",'id');
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
    
    public function getAliasAttribute() {
        if($this->address_as_name) {
            $address = $this->address->format;
            $dot = str_replace(".", " ", $address);
            $comma = str_replace(",", " ", $dot);
            $trim = trim($comma);
            $alias = strtolower(str_replace(" ","-", $trim) . "-$this->id"); 
        }
        else {
            $address = $this->address;
            $alias = strtolower(str_replace(" ","-", trim("$this->name-$address->city-$this->id")));
        }
        return $alias;
    }
    
    public function getStatusNameAttribute() {
        if($this->status == 'rfo') {
            $statusName = "Ready For Occupancy";
        }
        else if($this->status == 'ps') {
            $statusName = "Preselling";
        }
        else if($this->status == 'so') {
            $statusName = "Sold Out";
        }
        else {
            $statusName = "Unavailable";
        }
        return $statusName;
    }
    
    public function getPhotosCountAttribute() {
        $count = 0;
        if(!is_null($this->photo)) {
            $count++;
        }
        $count += $this->photos->count();
        return $count;
    }
    
    public function getAddressNameAttribute() {
        if($this->address_as_name) {
            $address = $this->address;
            $name = "$address->address, $address->city $address->zip";
            return $name;
        }
        else {
            return $this->name;
        }
    }
}

