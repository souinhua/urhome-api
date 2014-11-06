<?php
/**
 * Description of Developer
 *
 * @author User
 */
class Developer extends Eloquent {
    
    protected $table = 'developer';
    public $timestamps = false;
    
    public function photo() {
        return $this->hasOne('Photo', 'id', 'photo_id');
    }
    
    public function properties() {
        return $this->hasMany('Property');
    }
    
}
