<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fan extends Model
{
      protected $table = 'Fans';
      protected $fillable = array('id'
      ,'fan'
      ,'fanhead'
      ,'fansex'
      ,'fanname'
      ,'idol'
      ,'idolhead'
      ,'idolsex'
      ,'idolname');
}
