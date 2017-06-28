<?php

namespace App\Api\Controllers;

use App\Api\Controllers\BaseController;
use App\Api\Controllers\QiniuController;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Album;
use JWTAuth;
use DB;

class AlbumController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }

    //新键
    public function new(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request,[
        'albumname'=>'required',
        'albumstyle'=>'required|Numeric',
        'albumtype'=>'required|Numeric',
      ]);
      $input = $request->all();
      $input['moka'] = $role['moka'];
      $input['img'] = 'head/timg.jpeg';
      $result = Album::create($input);
      $result = $this->returnMsg('200',"album id:".$result['id'],$result['id']);
      return response()->json($result);
    }
    //查看
    public function detail(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request, [
        'id' => 'required|Numeric',
      ]);
      $id = $request->input('id');
      $result['cover'] = DB::table('Album')
        ->where('id',$id)
        ->get();
        if ($result['cover']->count()==0) {
          $result = $this->returnMsg('500',"id error");
          return response()->json($result);
        }
      $result['photos'] = DB::table('Photos')
        ->where('mokaid',$id)
        ->where('act',2)
        ->select('id','img_s')
        ->get();

      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }
    //删除一张
    public function deleteone(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request, [
        'id' => 'required|Numeric',
      ]);
      $id = $request->input('id');
      $result = DB::table('Photos')
        ->where('id',$id)
        ->where('act',2);
      $data = json_decode($result->get(),true);
      $data = $data[0];
        $album = DB::table('Album')
          ->where('id',$data['id']);
        $albummoka =$album->pluck('moka');
      if ($albummoka[0]!=$role['moka']) {
        $result = $this->returnMsg('500',"not yours");
        return response()->json($result);
      }
      if ($result->get()->count() == 0) {
        $result = $this->returnMsg('500',"id error");
        return response()->json($result);
      }
      $img_snum = $result->pluck('img_snum');
      $img_lnum = $result->pluck('img_lnum');
      QiniuController::deleteone($img_snum);
      QiniuController::deleteone($img_lnum);
      $result = $result ->delete();
      $sum = DB::table('Photos')
        ->where('mokaid',$id)
        ->where('act',2)
        ->get()->count();
      $album ->update(['sum'=>$sum]);
      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }
    //删除所有
    public function deleteall(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request, [
        'id' => 'required|Numeric',
      ]);
      $id = $request->input('id');
      $album = DB::table('Album')
        ->where('id',$id);
        $albummoka =$album->pluck('moka');
      if ($albummoka[0]!=$role['moka']) {
        $result = $this->returnMsg('500',"not yours");
        return response()->json($result);
      }
      if ($album->get()->count()!=0) {
          $num = $album->pluck('sum');
          if ($num[0]!=0) {
            QiniuController::deleteall('mokaalbum'.$role['moka'].$id);
          }
        }
      $album->delete();
      $result = DB::table('Photos')
        ->where('mokaid',$id)
        ->where('act',2)
        ->delete();
      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }
    //列表
    public function my(Request $request){
      $role = JWTAuth::toUser();
      $moka = $request->input('moka',$role['moka']);
      $page = $request->input('page',1);
      $result = DB::table('Album')
        ->where('moka',$moka)
        ->orderBy('id','desc')
        ->select('id','img','sum')
        ->skip(($page-1)*6)
        ->limit('6')
        ->get();
      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }
}
