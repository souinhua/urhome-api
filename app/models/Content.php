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
        return $query->whereNull('publish_start')->whereNull('publish_end');
    }

    public function scopePublished($query) {
        $dateStr = date("Y-m-d H:i:s", time());
        $date = DB::getPdo()->quote($dateStr);
        return $query->whereRaw("publish_start IS NOT NULL AND if(publish_end IS NOT NULL, publish_end > ?, TRUE)", array($date));
    }

    public function scopeOverdue($query) {
        $dateStr = date("Y-m-d H:i:s", time());
        $date = DB::getPdo()->quote($dateStr);
        return $query->whereRaw("publish_start IS NOT NULL AND if(publish_end IS NOT NULL, publish_end < ?, TRUE)", array($date));
    }
    
    /*
     * Content Relationships 
     */
    
    public function photo() {
        return $this->hasOne('Photo', 'id', 'photo_id');
    }
    
    public function created_by() {
        return $this->hasOne("User", "id", "created_by_id");
    }
    
    public function edited_by() {
        return $this->hasOne("User", "id", "updated_by_id");
    }
    
    public function published_by() {
        return $this->hasOne("User", "id", "published_by");
    }
    
    public function properties() {
        return $this->belongsToMany('Property','content_property','property_id','content_id');
    }
}
