<?php

namespace App\Api\Controllers;

use App\Api\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Status;
use JWTAuth;
use DB;

class StatusController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }

    //开启新业务
    public function start(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request,[
        'target'=>'required|Numeric',
        'target_id'=>'required|Numeric',
      ]);
      $target = $request->input('target');
      $target_id = $request->input('target_id');
      $create = DB::table('Records')
        ->where('target',$target)
        ->where('target_id',$target_id);
        if ($create->get()->count() == 0) {
          $result = $this->returnMsg('500','error target');
          return response()->json($result);
        }
        $create = $create
        ->pluck('moka');
      $input['boss'] = $create[0];
      $input['customer'] = $role['moka'];
      $input['target_id'] = $target_id;
      $input['target'] = $target;
      $result = Status::create($input);
      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }
}
