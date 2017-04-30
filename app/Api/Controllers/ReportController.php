<?php

namespace App\Api\Controllers;

use App\Api\Controllers\BaseController;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Report;
use JWTAuth;
use DB;

class ReportController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }

    //举报
    public function report(Request $request){
      $role = JWTAuth::toUser();
      $input['content'] = $request->input('content',null);
      $input['moka'] = $role['moka'];
      $result = Report::create($input);
      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }
}
