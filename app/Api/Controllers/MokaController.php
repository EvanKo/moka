<?php

namespace App\Api\Controllers;

use App\Api\Controllers\BaseController;
use Illuminate\Support\Facades\Session;
use Curl\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Order;
use JWTAuth;
use DB;

class MokaController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    //发摩卡
    public function make(Request $request){
      $role = JWTAuth::toUser();

    }
    //删除摩卡
    public function delete(Request $request){
      $role = JWTAuth::toUser();

    }

}
