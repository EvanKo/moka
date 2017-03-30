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
          return 'unfinished';
        }
      $size = $request->input('size',null);
      $imgnum = $request->input('imgnum',null);
      $num = md5(time()).rand(1,9);
      $root = public_path().'/photo/moka/'.$num.'/';
      if(!file_exists($root)){
        mkdir($root);
      }
      $input['moka'] = $role['moka'];
      $input['size'] = $size;
      $input['imgnum'] = $imgnum;
      $input['mokanum'] = $num;
      $result = Moka::create($input);
      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }

    //删除或取消摩卡
    public function delete(Request $request){
      $role = JWTAuth::toUser();

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
