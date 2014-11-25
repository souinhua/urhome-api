<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Amenity
 *
 * @author User
 */
class Amenity extends Eloquent {

    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];
    protected $table = 'amenity';

    public function photo() {
        return $this->hasOne('Photo', 'id', 'photo_id');
    }

}
