<?php

namespace App\Api\Controllers;

use App\Api\Controllers\BaseController;
use App\Api\Controllers\AppreciateController;
use App\Api\Controllers\CommentController;
use App\Api\Controllers\CommonController;
use Illuminate\Support\Facades\Session;
use Curl\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Record;
use App\Activity;
use JWTAuth;
use DB;
use File;

class ActivityController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }

    //发活动
    public function make(Request $request){
      $img = $request->file('img',null);
      $content = $request->input('content',null);
      $role = JWTAuth::toUser();
      $moka = $role['moka'];
      if ($img != null) {
        $root = public_path().'/photo/activity/'.$moka.'/';
        $root2 = '/photo/activity/'.$moka.'/';
        if(!file_exists($root)){
          mkdir($root);
        }
        $num = md5(time()).".".$img->getClientOriginalExtension();
        $img->move( $root,$num);
        $input['img'] = $_SERVER['HTTP_HOST'].$root2.$num;
      }
      $input['content'] = $content;
      $input['moka'] = $moka;
      // $input['pass'] = '1';
      $input['area'] = $role['area'];
      $result = Activity::create($input);
      // $result = json_decode($result,true);
      // $input['target_id'] = $result['id'];
      // $input['target'] = 4;
      // $result = Record::create($input);
      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }
    //删除活动
    public function delete(Request $request){
      $role = JWTAuth::toUser();
      $id = $request->input('id',null);
      if ($id == null) {
        $result = $this->returnMsg('500',"momentid require");
        return response()->json($result);
      }
      $object = Activity::find($id);
      if (!$object) {
        $result = $this->returnMsg('500',"momentid error");
        return response()->json($result);
      }
      $num = $object['img'];
      if ($num != '') {
        $num = trim($num,$_SERVER['HTTP_HOST']);
        File::delete(public_path().$num);
      }
      AppreciateController::deleall(4,$object['id']);
      CommentController::deleall(4,$object['id']);
      $result =  $object->delete();
      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }

    //地区活动
    public function areaactivity(Request $request){
        $role = JWTAuth::toUser();
        $area = $role['area'] == null ? 1:$role['area'];
        $page = $request->input('page',1);
        $area = $request->input('area',$area);
        $record = DB::table('Activities')
          ->where('area',$area)
          // ->where('pass','1')
          ->orderBy('id','desc')
          ->skip(($page-1)*10)
          ->limit(10)
          ->select('img','area','content','moka')
          ->get();
        $flows = json_decode($record,true);
        $num = 0;
        if ($record->count() == 0) {
          $result = $this->returnMsg('200','bottum');
          return response()->json($result);
        }
        foreach ($flows as $key ) {
             $row[$num]['data'] = $key;
             $row[$num++]['author'] = CommonController::self($key['moka']);
         }
        $result = $this->returnMsg('200','ok',$row);
        return response()->json($result);
    }
}
