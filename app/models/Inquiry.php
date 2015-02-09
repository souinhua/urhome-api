<?php

/**
 * Description of Inquiry
 *
 * @author User
 */
class Inquiry extends Eloquent {
    
    protected $table = "inquiry";
    public $timestamps = false;
    
    public function property() {
        return $this->hasOne('Property', 'id', 'property_id');
    }
}
