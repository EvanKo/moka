<?php

namespace App\Api\Controllers;


use DB;
use App\Api\Controllers\BaseController;
use Illuminate\Support\Facades\Session;
use Curl\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Appreciate;
use JWTAuth;

class AppreciateController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    //执行点赞或取消点赞
    public function handle(Request $request){
      $role = JWTAuth::toUser();
      $target = $request->input('kind',null);
      $target_id = $request->input('key',null);
      if ($target == null || $target_id == null ) {
        $result = $this->returnMsg('500','request error');
        return response()->json($result);
      }
      $query = 'target = '.$target.' and target_id = '.$target_id.' and moka = '.$role['moka'];
      $object = Appreciate::whereRaw($query);
      if (!$object->get()->isEmpty()) {
        $result = $object->delete();
        $result = $this->returnMsg('200','disappreciated',$result);
        return response()->json($result);
      }
      else {
        $input = $request->all();
        $input['moka'] = $role['moka'];
        $input['head'] = $role['head'];
        $result = Appreciate::create($input);
        $result = $this->returnMsg('200','appreciated',$result);
        return response()->json($result);
      }
    }
    //赞列表，有上限
    public static function list($target,$target_id,$num = null){
      $object = DB::table('Appreciates')
        ->where('target',$target)
        ->where('target_id',$target_id)
        ->select('id','moka','head');
        if (!$num) {
          $result['data'] = $object->limit($num)->get();
        }
      $result['data'] = $object->get();
      $result['sum'] = $object->count();
      return $result;
    }
    //赞列表，有上限
    public function alllist(Request $request){
      $page = $request->input('page',1);
      $target = $request->input('kind',null);
      $target_id = $request->input('key',null);
      if ($target == null || $target_id == null ) {
        $result = $this->returnMsg('500','request error');
        return response()->json($result);
      }
      $result = DB::table('Appreciates')
        ->join('Roles','Appreciates.moka','=','Roles.moka')
        ->orderBy('Appreciates.id','desc')
        ->where('Appreciates.target',$target)
        ->where('Appreciates.target_id',$target_id)
        ->select('Appreciates.moka','Roles.id','Roles.name','Roles.sex','Roles.province','Roles.city')
        ->skip(($page-1)*15)
        ->limit(15)
        ->get();
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
  }
    //删除赞
    public static function deleall($target,$target_id){
      $query = 'target = '.$target.' and target_id = '.$target_id;
      $object = Appreciate::whereRaw($query);
      $result = $object->delete();
      return $result;
    }
}
