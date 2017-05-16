<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
	protected $table = 'Friends';
      protected $fillable = array(
  'frienda'
  ,'friendb'
);
}
