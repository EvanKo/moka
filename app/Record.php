<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
      protected $table = 'Records';
      protected $fillable = array(
        'moka'
        ,'area'
        ,'view'
        ,'target'
        ,'target_id'
);
}
