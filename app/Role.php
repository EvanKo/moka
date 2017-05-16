<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
	protected $table = 'Roles';
      protected $fillable = array('tel','moka'
      ,'login'
      ,'level'
      ,'sex'
      ,'v'
      ,'fee'
      ,'role'
      ,'lastest'
      ,'fans'
      ,'idols'
      ,'name'
      ,'province'
      ,'city'
      ,'head'
      ,'bgimg'
      ,'password'
      ,'intro'
      ,'area');
}
