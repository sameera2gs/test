<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class department extends Model
{
    //
    protected $table = 'departments';
    //protected $guarded = ['api_token'];
    protected $guarded = [];

     public function ldap_users(){
         return $this->hasMany(Ldapuser::class);
     }
}
