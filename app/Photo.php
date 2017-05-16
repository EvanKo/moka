<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
	protected $table = 'Photos';
      protected $fillable = array(
      'mokaid'
      ,'imgnum'
      ,'img_s'
      ,'img_snum'
      ,'img_l'
      ,'img_lnum'
      ,'fee'
      ,'view'
      );
}
