<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Address
 *
 * @author User
 */
class Address extends Eloquent{
    
    protected $table = 'address';
    public $timestamps = false;
    
    protected $appends = array("format");
    
    public function getFormatAttribute() {
        return "$this->address, $this->city, $this->province $this->zip";
    }
}
