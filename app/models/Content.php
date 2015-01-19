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
    
    /*
     * Content Scopes
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
     * Content Relationships 
     */
    
    public function photo() {
        return $this->hasOne('Photo', 'id', 'photo_id');
    }
    
    public function creator() {
        return $this->hasOne("User", "id", "created_by");
    }
    
    public function editor() {
        return $this->hasOne("User", "id", "updated_by");
    }
    
    public function publisher() {
        return $this->hasOne("User", "id", "published_by");
    }
    
    public function properties() {
        return $this->belongsToMany('Property','content_property','property_id','content_id');
    }
}
