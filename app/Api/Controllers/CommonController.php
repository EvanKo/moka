<?php

namespace App\Api\Controllers;

use App\Api\Controllers\BaseController;
use App\Api\Controllers\AppreciateController;
use App\Api\Controllers\CommentController;
use Illuminate\Support\Facades\Session;
use Curl\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Order;
use App\Moment;
use App\Moka;
use App\Activity;
use JWTAuth;
use DB;

class CommonController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    public static function self($id){
      $role = DB::table('Roles')->where('moka',$id)
        ->select('id','moka', 'name','head','sex','role','province','city')
        ->first();
      return $role;
    }

    //动态详情
    public function moment(Request $request){
      $id = $request->input('id',null);
      if ($id == null) {
        $result = $this->returnMsg('500','request error');
        return response()->json($result);
      }
      $moment = DB::table('Moments')->where('id',$id)
        ->select('id','moka','content','img','view','created_at');
      if ($moment->count() == 0) {
        $result = $this->returnMsg('500','id error');
        return response()->json($result);
      }
      $data = $moment->get();
      $data = json_decode($data,true);
      $data = $data[0];
      $data['view'] += 1;
      $moment->update(['view'=>$data['view']]);
      DB::table('Records')->where('target_id',$id)
        ->where('target',1)
        ->update(['view'=>$data['view']]);
      $zan = AppreciateController::list(1,$id,10);
      $result['zan'] = $zan;
      $result['moment'] = $data;
      $result['author'] = CommonController::self($data['moka']);
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }
    //订单详情
    public function order(Request $request){
      $id = $request->input('id',null);
      if ($id == null) {
        $result = $this->returnMsg('500','request error');
        return response()->json($result);
      }
      $order = DB::table('Orders')->where('id',$id)
        ->select('view','content','type','price','img','moka','id');
      if ($order->count() == 0) {
        $result = $this->returnMsg('500','id error');
        return response()->json($result);
      }
      $data = $order->get();
      $data = json_decode($data,true);
      $data = $data[0];
      $data['view'] += 1;
      $order->update(['view'=>$data['view']]);
      DB::table('Records')->where('target_id',$id)
        ->where('target',2)
        ->update(['view'=>$data['view']]);
      $zan = AppreciateController::list(2,$id,10);
      $result['zan'] = $zan;
      $result['order'] = $data;
      $result['author'] = CommonController::self($data['moka']);
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }
    //摩卡详情
    public function moka(Request $request){
      $id = $request->input('id',null);
      if ($id == null) {
        $result = $this->returnMsg('500','request error');
        return response()->json($result);
      }
      $moka = DB::table('Mokas')->where('id',$id)
        ->select('moka','mokaid','size','imgnum','view','id');
      if ($moka->count() == 0) {
        $result = $this->returnMsg('500','id error');
        return response()->json($result);
      }
      $data = $moka->get();
      $data = json_decode($data,true);
      $data = $data[0];
      $mokaid =  $data['mokaid'];
      $photos = DB::table('Photos')
        ->where('mokaid',$mokaid)
        ->orderBy('imgnum')
        ->select('Photos.id','Photos.imgnum','Photos.img_s')
        ->get();
      $data['view'] += 1;
      $moka->update(['view'=>$data['view']]);
      DB::table('Records')->where('target_id',$id)
        ->where('target',3)
        ->update(['view'=>$data['view']]);
      $zan = AppreciateController::list(3,$id,10);
      $result['zan'] = $zan;
      $result['moka'] = $data;
      $result['photos'] = $photos;
      $result['author'] = CommonController::self($data['moka']);
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }
    //活动详情
    public function activity(Request $request){
      $id = $request->input('id',null);
      if ($id == null) {
        $result = $this->returnMsg('500','request error');
        return response()->json($result);
      }
      $activity = DB::table('Activities')->where('id',$id)
        ->select('id','moka','title','content','view','start','end','price','type','local');
      if ($activity->count() == 0) {
        $result = $this->returnMsg('500','id error');
        return response()->json($result);
      }
      $data = $activity->get();
      $data = json_decode($data,true);
      $data = $data[0];
      $data['view'] += 1;
      $activity->update(['view'=>$data['view']]);
      $data['photo'] = DB::table('Photos')->where('mokaid',$id)
        ->where('act',1)
        ->orderBy('imgnum')
        ->select('id','img_l','imgnum')
        ->get();
      $zan = AppreciateController::list(4,$id,10);
      $result['zan'] = $zan;
      $result['activity'] = $data;
      $result['author'] = CommonController::self($data['moka']);
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }

    //个人纪录
    public function selfrecord(Request $request){
        $role = JWTAuth::toUser();
        $page = $request->input('page',1);
        $moka = $request->input('moka',$role['moka']);
        $record = DB::table('Records')
          ->where('moka',$moka)
          ->orderBy('id','desc')
          ->skip(($page-1)*10)
          ->limit(10)
          ->get();
        $flows = json_decode($record,true);
      $num = 0;
        if ($record->count() == 0) {
          $result = $this->returnMsg('200','bottom');
          return response()->json($result);
        }
       foreach ($flows as $key ) {
            $row[$num++] = CommonController::detail($key['target'],$key['target_id']);
        }
        $result = $this->returnMsg('200','ok',$row);
        return response()->json($result);
    }



    //附近
    public function near(Request $request){
        $role = JWTAuth::toUser();
        $area = $role['area'] == null ? 1:$role['area'];
        $page = $request->input('page',1);
        $area = $request->input('area',$area);
        $record = DB::table('Records')
          ->where('area',$area)
          ->orderBy('id','desc')
          ->skip(($page-1)*10)
          ->limit(10)
          ->get();
        $flows = json_decode($record,true);
      $num = 0;
        if ($record->count() == 0) {
          $result = $this->returnMsg('200','bottom');
          return response()->json($result);
        }
       foreach ($flows as $key ) {
            $row[$num++] = CommonController::detail($key['target'],$key['target_id']);
        }
        $result = $this->returnMsg('200','ok',$row);
        return response()->json($result);
    }
    //好友
    public function friend(Request $request){
      $role = JWTAuth::toUser();
      $page = $request->input('page',1);
      $moka = DB::table('Friends')
        ->where('frienda',$role['moka'])
        ->pluck('friendb');
      $record = DB::table('Records')
        ->where('moka',$role['moka'])
        ->orwhere('moka',$moka)
        ->orderBy('id','desc')
        ->skip(($page-1)*10)
        ->limit(10)
        ->get();
      $flows = json_decode($record,true);
      $num = 0;
      if ($record->count() == 0) {
        $result = $this->returnMsg('200','bottom');
        return response()->json($result);
      }
     foreach ($flows as $key ) {
          $row[$num++] = CommonController::detail($key['target'],$key['target_id']);
      }
      $result = $this->returnMsg('200','ok',$row);
      return response()->json($result);
    }
    //热门
    public function hot(Request $request){
      $role = JWTAuth::toUser();
      $page = $request->input('page',1);
      $record = DB::table('Records')
        ->orderBy('view','desc')
        ->orderBy('created_at','desc')
        ->skip(($page-1)*10)
        ->limit(10)
        ->get();
      $flows = json_decode($record,true);
      $num = 0;
      if ($record->count() == 0) {
        $result = $this->returnMsg('200','bottom');
        return response()->json($result);
      }
     foreach ($flows as $key ) {
          $row[$num++] = CommonController::detail($key['target'],$key['target_id']);
      }
      $result = $this->returnMsg('200','ok',$row);
      return response()->json($result);
    }
    //每条记录详细
    protected function detail($target,$target_id){
      switch ($target) {
        case 1:
          $moment = DB::table('Moments')
            ->where('id',$target_id);
          $view = json_decode($moment->get(),true);
          $moka = $view[0]['moka'];
          $view = $view[0]['view']+1;
          $moment->update(['view'=>$view]);
          $result['moment'] = $moment->first();
          $result['author'] = CommonController::self($moka);
          $result['zan'] = AppreciateController::list(1,$target_id,10);
          $result['comments'] = CommentController::two($target,$target_id);
          DB::table('Records')->where('target_id',$target_id)
            ->where('target',1)
            ->update(['view'=>$view]);
          break;
        case 2:
          $order = DB::table('Orders')
            ->where('id',$target_id);
          $view = json_decode($order->get(),true);
          $moka = $view[0]['moka'];
          $view = $view[0]['view']+1;
          $order->update(['view'=>$view]);
          $result['order'] = $order->first();
          $result['author'] = CommonController::self($moka);
          $result['zan'] = AppreciateController::list(2,$target_id,10);
          $result['comments'] = CommentController::two($target,$target_id);
          DB::table('Records')->where('target_id',$target_id)
            ->where('target',2)
            ->update(['view'=>$view]);
          break;
        case 3:
          $moka = DB::table('Mokas')
            ->where('id',$target_id);
          $data = $moka->get();
          $data = json_decode($data,true);
          $data = $data[0];
          $mokaid = $data['mokaid'];
          $photos = DB::table('Photos')
              ->where('mokaid',$mokaid)
              ->select('Photos.id','Photos.imgnum','Photos.img_s')
              ->get();
          $data['view'] += 1;
          $moka->update(['view'=>$data['view']]);
          DB::table('Records')->where('target_id',$target_id)
              ->where('target',3)
              ->update(['view'=>$data['view']]);
          $result['moka'] = $data;
          $result['photos'] = $photos;
          $result['author'] = CommonController::self($data['moka']);
          $result['zan'] = AppreciateController::list(3,$target_id,10);
          $result['comments'] = CommentController::two($target,$target_id);
          break;
        default:
          $result = $this->returnMsg('500','error');
          break;
      }
      return $result;
    }
}
