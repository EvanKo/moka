<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Moment extends Model
{
      protected $table = 'Moments';
      protected $fillable = array('moka','content','img','imgnum','view','area');
}
