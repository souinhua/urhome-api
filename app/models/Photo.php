<?php

/**
 * Description of Photo
 *
 * @author Janssen Canturias
 */
class Photo extends Eloquent {

    protected $table = 'photo';
    protected $guarded = array('path');

    public function delete() {
        try {
            $api = new \Cloudinary\Api();
            $api->delete_resources(array(
                $this->public_id
            ));
        } catch (Exception $e) {
            
        }
        parent::delete();
    }

    public function uploader() {
        return $this->belongsTo('User', 'uploaded_by');
    }

}
