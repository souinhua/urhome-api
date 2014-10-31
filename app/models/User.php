<?php

use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserTrait;

class User extends Eloquent implements UserInterface {

    use UserTrait,RemindableTrait,SoftDeletingTrait;

    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

//    protected $hidden = array('password', 'remember_token');
    
    /**
     * Returns the Address of the User
     *
     * @return ACL Object
     */
    public function address() {
        return $this->hasOne('Address','id','address_id');
    }
    
    /**
     * Returns the ACL of the User
     *
     * @return ACL Onject
     */
    public function acl() {
        return $this->hasOne('ACL','id','acl_id');
    }
    
    /**
     * Returns the ACL of the User
     *
     * @return ACL Onject
     */
    public function photo() {
        return $this->hasOne('Photo','id','photo_id');
    }

}
