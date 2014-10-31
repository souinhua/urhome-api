<?php

/**
 * Description of Photo
 *
 * @author Janssen Canturias
 */
class Photo extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'photo';
    protected $guarded = array('path');

}
