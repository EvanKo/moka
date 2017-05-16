<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
	protected $table = 'Activities';
      protected $fillable = array('moka','img','title','area','content','view','pass');
}
