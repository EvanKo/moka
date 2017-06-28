<?php

namespace App\Api\Controllers;

use App\Api\Controllers\BaseController;
use App\Api\Controllers\AppreciateController;
use App\Api\Controllers\CommentController;
use App\Api\Controllers\CommonController;
use App\Api\Controllers\QiniuController;
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
      $input['img'] = 'head/timg.jpeg';
      $result = Activity::create($input);
      $num = json_decode($result,true);
      $num = $num['id'];
      $result = $this->returnMsg('200',"activity id:".$num,$num);
      return response()->json($result);
    }

    //删除或取消通告
    public function delete(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request,[
        'id'=>'required',
      ]);
      $id = $request->input('id');
      $activity = DB::table('Activities')
        ->where('id',$id);
        $activitymoka =$activity->pluck('moka');
      if ($activitymoka[0]!=$role['moka']) {
        $result = $this->returnMsg('500',"not yours");
        return response()->json($result);
      }
      QiniuController::deleteall('activity'.$role['moka'].''.$id.'');
      DB::table('Photos')
        ->where('mokaid',$id)
        ->where('act',1)
        ->delete();
      // $root = public_path().'/photo/activity/'.$id.'/';
      // if(file_exists($root)){
      //   ActivityController::deldir($root);
      // }
      DB::table('Activities')
        ->where('id',$id)
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
        $result = $this->returnMsg('500','mokaactivity haved saved');
        return response()->json($result);
      }
      $activity->update($request->all());
      $activity->update(['finish'=>1]);
      $input['target']=4;
      $input['target_id']=$request->input('id');
      $input['moka']=$role['moka'];
      $input['area']=$role['area'];
      Record::create($input);
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
        $area = $role['area'] == null ? 7:$role['area'];
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

    //报名管理
    public function enroll(Request $request){
        $role = JWTAuth::toUser();
        $this->validate($request,[
          'id'=>'required|Numeric'
        ]);
        $id = $request->input('id');
        $page = $request->input('page',1);
        $moka = $request->input('moka',$role['moka']);
        $check = DB::table('Status')
          ->where('target',4)
          ->where('target_id',$id);
          if ($check->get()->count() == 0) {
            $result = $this->returnMsg('200','ok');
            return response()->json($result);
          }
        $own = $check->pluck('boss');
        if ($own[0] != $role['moka']) {
          $result = $this->returnMsg('500','not yours');
          return response()->json($result);
        }
        $customer = $check->pluck('customer');
        $result = DB::table('Status')
        ->leftjoin('Roles','Roles.moka','=','Status.customer')
          // ->leftjoin('Status','Status.customer','=','Roles.moka')
          ->where('Status.target',4)
          ->where('Status.target_id',$id)
          // ->where('Roles.moka',$customer)
          ->select('Status.id','Roles.name','Roles.head','Roles.role','Status.status')
          ->get();
        $result = $this->returnMsg('200','ok',$result);
        return response()->json($result);
    }
}
