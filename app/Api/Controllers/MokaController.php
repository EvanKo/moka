<?php

namespace App\Api\Controllers;

use App\Api\Controllers\BaseController;
use Illuminate\Support\Facades\Session;
use Curl\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Moka;
use JWTAuth;
use DB;

class MokaController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    //开始编辑摩卡
    public function start(Request $request){
      $role = JWTAuth::toUser();
      $object = DB::table('Mokas')
        ->where('moka',$role['moka'])
        ->where('finish','0')
        ->get();
      if ($object->count() != 0) {
          $photo  = DB::table('Mokas')
            ->where('moka',$role['moka'])
            ->where('finish','0')
            ->orderBy('id','desc')
            ->limit(1)
            ->pluck('mokaid');
          $photos = DB::table('Photos')
            ->where('mokaid',$photo)
            ->select('Photos.id','Photos.imgnum','Photos.img_s')
            ->get();
          $moka = DB::table('Mokas')
            ->where('moka',$role['moka'])
            ->where('finish','0')
            ->orderBy('id','desc')
            ->limit(1)
            ->select('Mokaid','imgrealnum','imgnum')
            ->get();
          $result['photos'] = $photos;
          $result['moka'] = $moka;
          $result = $this->returnMsg('200',"ok",$result);
          return response()->json($result);
        }
      $size = $request->input('size',null);
      $imgnum = $request->input('imgnum',null);
      if ( $this->returnReq($size,'size') != '200') {
        return $this->returnReq($size,'size');
      }
      if ( $this->returnReq($imgnum,'imgnum') != '200') {
        return $this->returnReq($imgnum,'imgnum');
      }
      $num = md5(time()).rand(1,9);
      $root = public_path().'/photo/moka/'.$num.'/';
      if(!file_exists($root)){
        mkdir($root);
      }
      $input['moka'] = $role['moka'];
      $input['size'] = $size;
      $input['imgnum'] = $imgnum;
      $input['mokaid'] = $num;
      $result = Moka::create($input);
      $result = $this->returnMsg('200',"ok",$num);
      return response()->json($result);
    }

    //删除或取消摩卡
    public function delete(Request $request){
      $role = JWTAuth::toUser();
      $mokaid = $request->input('mokaid',null);
      if ( $this->returnReq($mokaid,'mokaid') != '200') {
        return $this->returnReq($mokaid,'mokaid');
      }
      DB::table('Photos')
        ->where('mokaid',$mokaid)
        ->delete();
      $root = public_path().'/photo/moka/'.$mokaid.'/';
      if(file_exists($root)){
        MokaController::deldir($root);
      }
      DB::table('Mokas')
        ->where('mokaid',$mokaid)
        ->delete();
      return 'ok';
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
}
