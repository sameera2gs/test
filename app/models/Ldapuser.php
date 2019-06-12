<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use App\Models\department;

class Ldapuser extends Model
{
    //
    protected $table = 'ldap_users';
    //protected $guarded = ['api_token'];
    protected $guarded = [];

    public function department()
    {
        return $this->belongsTo(department::class);
    }
}
