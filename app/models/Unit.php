<?php

/**
 * Description of Unit
 *
 * @author Janssen Canturias
 */
class Unit extends Eloquent {
   
    protected $table = 'unit';
    public $timestamps = false;
    
    public function property() {
        return $this->belongsTo('Property');
    }
    
    public function features() {
        return $this->belongsToMany('Feature','unit_feature','unit_id','feature_id');
    }
    
    public function specs() {
        return $this->belongsToMany('Spec','unit_spec','unit_id','spec_id');
    }
    
    public function photo() {
        return $this->hasOne('Photo','id','photo_id');
    }
    
    public function details() {
        return $this->hasOne('CommonDetails','id','common_details_id');
    }
    
    public function photos() {
        return $this->belongsToMany('Photo','unit_photo','unit_id','photo_id');
    }
}
