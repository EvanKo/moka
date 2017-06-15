<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Moka extends Model
{
      protected $table = 'Mokas';
      protected $fillable = array(
        'moka'
      ,'mokaid'
      ,'area'
      ,'size'
      ,'imgnum'
      ,'imgrealnum'
      ,'finish'
      ,'view'
      );
}
