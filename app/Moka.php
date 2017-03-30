<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Moka extends Model
{
      protected $fillable = array(
        'moka'
      ,'mokanum'
      ,'area'
      ,'size'
      ,'imgnum'
      ,'imgrealnum'
      ,'finish'
      ,'view'
      );
}
