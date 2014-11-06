<?php

class Property extends Eloquent {
    
    use SoftDeletingTrait;
    
    protected $table = "property";
    
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
        return $this->hasOne('CpmmonDetails','id','common_details_id');
    }
    
    public function photos() {
        return $this->belongsToMany('Photo','property_photo','property_id','photo_id');
    }
}

