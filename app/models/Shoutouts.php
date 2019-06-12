<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Shoutouts extends Model
{
    //
    protected $table = 'shoutouts';
    //protected $guarded = ['api_token'];
    protected $guarded = [];

    function shoutout_files()
    {
    	return $this->hasMany(ShoutoutFiles::class);
    }
}