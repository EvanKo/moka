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

    //开始编辑摩卡
    public function start(Request $request){
      $role = JWTAuth::toUser();
      $object = DB::table('Activities')
        ->where('moka',$role['moka'])
        ->where('finish','0')
        ->get();
      if ($object->count() != 0) {
          $photo  = DB::table('Activities')
            ->where('moka',$role['moka'])
            ->where('finish','0')
            ->orderBy('id','desc')
            ->limit(1)
            ->pluck('id');
          $photos = DB::table('Photos')
            ->where('mokaid',$photo)
            ->where('act',1)
            ->orderBy('imgnum')
            ->select('id','Photos.imgnum','Photos.img_s')
            ->get();
          $activity = DB::table('Activities')
            ->where('moka',$role['moka'])
            ->where('finish','0')
            ->orderBy('id','desc')
            ->limit(1)
            ->get();
          $result['photos'] = $photos;
          $result['activity'] = $activity;
          $result = $this->returnMsg('200',"ok",$result);
          return response()->json($result);
        }
      $input['area']=$role['area'];
      $input['moka']=$role['moka'];
      $input['local']=$role['province'].$role['city'];
      $input['img'] = $_SERVER['HTTP_HOST'].'/photo/head/timg.jpeg';
      $result = Activity::create($input);
      $num = json_decode($result,true);
      $num = $num['id'];
      $root = public_path().'/photo/activity/'.$num.'/';
      if(!file_exists($root)){
        mkdir($root);
      }
      $result = $this->returnMsg('200',"activity id:".$num,$num);
      return response()->json($result);
    }

    //删除或取消通告
    public function delete(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request,[
        'id'=>'required',
      ]);
      $mokaid = $request->input('id',null);

      DB::table('Photos')
        ->where('mokaid',$mokaid)
        ->where('act',1)
        ->delete();
      $root = public_path().'/photo/activity/'.$mokaid.'/';
      if(file_exists($root)){
        ActivityController::deldir($root);
      }
      DB::table('Activities')
        ->where('id',$mokaid)
        ->delete();
      $result = $this->returnMsg('200',"deleted");
      return response()->json($result);
    }

    //保存
    public function save(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request,[
        'id'=>'required',
        'title'=>'required',
        'type'=>'required',
        'content'=>'required',
        'start'=>'required|date',
        'end'=>'required|date',
        'price'=>'required|Numeric',
      ]);
      $id = $request->input('id',null);
      $activity = DB::table('Activities')->where('id',$id);
      $finish = $activity->pluck('finish');
      if ($finish[0] == 1) {
        $result = $this->returnMsg('500','activity haved saved');
        return response()->json($result);
      }
      $activity->update($request->all());
      $activity->update(['finish'=>1]);
      $result = $this->returnMsg('200','saved');
      return response()->json($result);

    }

    protected static function deldir($dir) {
      //先删除目录下的文件：
      $dh=opendir($dir);
      while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
          $fullpath=$dir."/".$file;
          if(!is_dir($fullpath)) {
              unlink($fullpath);
          } else {
              deldir($fullpath);
          }
        }
      }

      closedir($dh);
      //删除当前文件夹：
      if(rmdir($dir)) {
        return true;
      } else {
        return false;
      }
    }

    //地区活动
    public function areaactivity(Request $request){
        $role = JWTAuth::toUser();
        $area = $role['area'] == null ? 1:$role['area'];
        $page = $request->input('page',1);
        $area = $request->input('area',$area);
        $type = $request->input('type',null);
        $record = DB::table('Activities')
          ->where('area',$area)
          ->where('finish',1);
        if ($type != null) {
          $record = $record->where('type',$type);
        }
          $record = $record->orderBy('id','desc')
          // ->where('pass','1')
          ->skip(($page-1)*10)
          ->limit(10)
          ->select('img','area','type','view','id','title','price')
          ->get();
        if ($record->count() == 0) {
          $result = $this->returnMsg('200','bottum');
          return response()->json($result);
        }
        $result = $this->returnMsg('200','ok',$record);
        return response()->json($result);
    }
}
