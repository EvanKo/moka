<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Auth extends Model
{
      protected $fillable = array(
        'moka'
        ,'authentication_name'
        ,'authentication'
        ,'identification'
        ,'identification_img'
        ,'bussiness_img'
    );
}
