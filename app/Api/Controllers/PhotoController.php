<?php

namespace App\Api\Controllers;

use App\Api\Controllers\BaseController;
// use App\Api\Controllers\PhotoController;
use App\Api\Controllers\CommentController;
use Illuminate\Support\Facades\Session;
use Curl\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Moka;
use App\Photo;
use JWTAuth;
use DB;
use File;

class PhotoController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    //打开大图
    public function detail(Request $request){
      $id = $request->input('id',null);
      if ( $this->returnReq($id,'id') != '200') {
        $result = $this->returnReq($id,'id');
        return response()->json($result);
      }
      $object = Photo::find($id);
      $result = DB::table('Photos')->where('id',$id)
        ->update(['view' => $object->view+1]);
      $result = DB::table('Photos')->where('id',$id)
        ->select('id','mokaid','img_s','img_l','fee','view')->get();
      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }

    //上传图片
    public function update(Request $request){
      $role = JWTAuth::toUser();
      $mokaid = $request->input('id',null);
      $img = $request->file('img',null);
      $num = $request->input('num',null);
      if ( $this->returnReq($num,'num') != '200') {
        return $this->returnReq($num,'num');
      }
      if ( $this->returnReq($img,'img') != '200') {
        return $this->returnReq($img,'img');
      }
      if ( $this->returnReq($mokaid,'id') != '200') {
        return $this->returnReq($mokaid,'id');
      }
      $root = public_path().'/photo/moka/'.$mokaid.'/';
      if(!file_exists($root)){
        mkdir($root);
      }
      $imgroot = $root.$num.".".$img->getClientOriginalExtension();
      $imgroot2 = $root.$num."s.".$img->getClientOriginalExtension();
      $img->move( $root,$num.".".$img->getClientOriginalExtension());
      PhotoController::small($imgroot,200,200,$imgroot2,$img->getClientOriginalExtension());
      $input['img_s'] = $_SERVER['HTTP_HOST'].'/photo/moka/'.$mokaid.'/'.$num."s.".$img->getClientOriginalExtension();
      $input['img_l'] = $_SERVER['HTTP_HOST'].'/photo/moka/'.$mokaid.'/'.$num.".".$img->getClientOriginalExtension();
      $input['img_snum'] = $imgroot2;
      $input['img_lnum'] = $imgroot;
      $input['mokaid'] = $mokaid;
      $input['imgnum'] = $num;
      $last = Photo::whereRaw('mokaid = \''.$mokaid.'\' and imgnum = '.$num);
      if (!$last->get()->isempty()) {
        $result = $last->update($input);
      }
      else{
        $result = Photo::create($input);
      }
      $real = DB::table('Photos')->where('mokaid',$mokaid)
        ->count();
      $real = intval($real);
      // return $real;
      $result = DB::table('Mokas')->where('mokaid',$mokaid)
        ->orderBy('id','desc')
        ->limit(1)
        // ->get();
        // return $result;
        ->update(['imgrealnum' => $real]);
      $result = $this->returnMsg('200',"ok",$input['img_s']);
      return response()->json($result);
    }

    //上传通告图片
    public function actupdate(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request,[
        'id'=>'required',
        'img'=>'required|Image',
        'num'=>'required|Numeric',
      ]);
      $id = $request->input('id');
      $img = $request->file('img');
      $num = $request->input('num');
      $root = public_path().'/photo/activity/'.$id.'/';
      if(!file_exists($root)){
        mkdir($root);
      }
      $imgroot = $root.$num.".".$img->getClientOriginalExtension();
      $imgroot2 = $root.$num."s.".$img->getClientOriginalExtension();
      $img->move( $root,$num.".".$img->getClientOriginalExtension());
      PhotoController::small($imgroot,200,200,$imgroot2,$img->getClientOriginalExtension());
      $input['img_s'] = $_SERVER['HTTP_HOST'].'/photo/activity/'.$id.'/'.$num."s.".$img->getClientOriginalExtension();
      $input['img_l'] = $_SERVER['HTTP_HOST'].'/photo/activity/'.$id.'/'.$num.".".$img->getClientOriginalExtension();
      $input['img_snum'] = $imgroot2;
      $input['img_lnum'] = $imgroot;
      $input['mokaid'] = $id;
      $input['imgnum'] = $num;
      $input['act'] = 1;
      if ($num == 1) {
        DB::table('Activities')->where('id',$id)
          ->where('finish',0)
          ->update(['img'=>$input['img_s']]);
      }
      $last = Photo::whereRaw('mokaid = \''.$id.'\' and imgnum = '.$num);
      if (!$last->get()->isempty()) {
        $result = $last->update($input);
      }
      else{
        $result = Photo::create($input);
      }

      $result = $this->returnMsg('200',"ok",$input['img_s']);
      return response()->json($result);
    }


    protected static function small($background, $width, $height, $newfile,$type) {
     list($s_w, $s_h)=\getimagesize($background);//获取原图片高度、宽度
     if ($width && ($s_w < $s_h)) {
     $width = ($height / $s_h) * $s_w;
     } else {
     $height = ($width / $s_w) * $s_h;
     }
     $new=\imagecreatetruecolor($width, $height);
     switch ($type) {
       case 'jpg':
        $img=\imagecreatefromjpeg($background);
        \imagecopyresampled($new, $img, 0, 0, 0, 0, $width, $height, $s_w, $s_h);
        \imagejpeg($new, $newfile);
         break;
         case 'jpeg':
          $img=\imagecreatefromjpeg($background);
          \imagecopyresampled($new, $img, 0, 0, 0, 0, $width, $height, $s_w, $s_h);
          \imagejpeg($new, $newfile);
           break;
           case 'png':
            $img=\imagecreatefrompng($background);
            \imagecopyresampled($new, $img, 0, 0, 0, 0, $width, $height, $s_w, $s_h);
            \imagepng($new, $newfile);
             break;
       default:
         return 500;
         break;
     }

     \imagedestroy($new);
     \imagedestroy($img);
    }

}
