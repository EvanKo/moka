<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
      protected $table = 'Album';
      protected $fillable = array(
        'moka'
        ,'img'
		,'sum'
		,'albumname'
		        ,'albumtype'
				        ,'albumstyle'
    );
}
