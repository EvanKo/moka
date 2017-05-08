<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
      protected $fillable = array('target','target_id','moka','answer','answername','content','author','head');
}
