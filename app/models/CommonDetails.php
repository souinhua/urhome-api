<?php

/**
 * Description of CommonDetails
 *
 * @author User
 */
class CommonDetails extends Eloquent {
    
    protected $table = 'common_details';
    public $timestamps = false;
    
    public function photos() {
        return $this->belongsToMany('Photo','plan_photo','common_details_id','photo_id');
    }
}
