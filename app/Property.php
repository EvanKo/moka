<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
      protected $table = 'Properties';
      protected $fillable = array(
        'moka'
        ,'member'
        ,'member_date'
        ,'member_last'
        ,'money'
      );
}
