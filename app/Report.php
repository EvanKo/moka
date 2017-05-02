<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
      protected $fillable = array(
        'moka',
        'content',
        'pending'
);
}
