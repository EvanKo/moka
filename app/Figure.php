<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Figure extends Model
{
      protected $fillable = array(
        'moka' ,
        'height' ,
        'weight' ,
        'hips' ,
        'bust' ,
        'waist' ,
        'shoe',
        'exp'
);
}
