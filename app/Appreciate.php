<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Appreciate extends Model
{
      protected $table = 'Appreciates';  
      protected $fillable = array('target','target_id','moka','head');
}
