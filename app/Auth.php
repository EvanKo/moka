<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Auth extends Model
{
      protected $table = 'Auths';
      protected $fillable = array(
        'moka'
        ,'realname'
        ,'company'
        ,'companyname'
        ,'idcardnumber'
        ,'img'
        ,'pass'
    );
}
