<?php

namespace App\Api\Controllers;

use App\Api\Controllers\BaseController;
use Illuminate\Support\Facades\Session;
use Curl\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use JWTAuth;

class MainpageController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    //所有数据
    public function main(){
      $role = JWTAuth::toUser();
      $result = $this->returnMsg('200',"ok",$role);
      return response()->json($result);
    }
}
