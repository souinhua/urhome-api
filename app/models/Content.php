<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Content
 *
 * @author User
 */
class Content extends Eloquent{
    
    use SoftDeletingTrait;
    
    protected $table = "content";
    
    public function photo() {
        return $this->hasOne('Photo', 'id', 'photo_id');
    }
    
    public function creator() {
        return $this->hasOne("Person", "id", "created_by");
    }
    
    public function editor() {
        return $this->hasOne("Person", "id", "updated_by");
    }
    
    public function publisher() {
        return $this->hasOne("Person", "id", "published_by");
    }
}
