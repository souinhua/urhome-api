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
        if (File::exists($this->path)) {
            File::delete($this->path);
        }
        parent::delete();
    }

}
