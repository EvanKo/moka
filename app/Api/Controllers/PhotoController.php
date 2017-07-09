<?php

namespace App\Api\Controllers;

use App\Api\Controllers\BaseController;
use App\Api\Controllers\QiniuController;
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

      $imgroot = 'mmoka'.$role['moka'].''.$mokaid.''.$num.md5(time()).".".$img->getClientOriginalExtension();
      $imgroot2 = 'mmoka'.$role['moka'].''.$mokaid.''.$num.md5(time())."s.".$img->getClientOriginalExtension();
      $new = public_path().'/'.$imgroot2;
      PhotoController::small($img,200,200,$new,$img->getClientOriginalExtension());
      // return $new;
      QiniuController::deleteall('mmoka'.$role['moka'].''.$mokaid.''.$num);
       $sending = QiniuController::update($img,$imgroot);
       if ($sending == 500) {
         $result = $this->returnMsg('500',"upload failed");
         return response()->json($result);
       }
       $sending = QiniuController::update($new,$imgroot2);
       if ($sending == 500) {
         $result = $this->returnMsg('500',"upload failed");
         return response()->json($result);
       }
      unlink($new);

      $input['img_s'] = ''.$imgroot2;
      $input['img_l'] = ''.$imgroot;

      $input['img_snum'] = $imgroot2;
      $input['img_lnum'] = $imgroot;
      $input['mokaid'] = $mokaid;
      $input['imgnum'] = $num;
      $input['moka'] = $role['moka'];
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
      $imgroot = 'activity'.$role['moka'].''.$id.''.$num.md5(time()).".".$img->getClientOriginalExtension();
      $imgroot2 = 'activity'.$role['moka'].''.$id.''.$num.md5(time())."s.".$img->getClientOriginalExtension();
      $new = public_path().'/'.$imgroot2;
      PhotoController::small($img,200,200,$new,$img->getClientOriginalExtension());
      // return $new;
      QiniuController::deleteall('activity'.$role['moka'].''.$id.''.$num);
      $sending = QiniuController::update($img,$imgroot);
      if ($sending == 500) {
        $result = $this->returnMsg('500',"upload failed");
        return response()->json($result);
      }
      $sending = QiniuController::update($new,$imgroot2);
      if ($sending == 500) {
        $result = $this->returnMsg('500',"upload failed");
        return response()->json($result);
      }
      unlink($new);
      $input['img_s'] = ''.$imgroot2;
      $input['img_l'] = ''.$imgroot;
      $input['img_snum'] = $imgroot2;
      $input['img_lnum'] = $imgroot;
      $input['mokaid'] = $id;
      $input['imgnum'] = $num;
      $input['act'] = 1;
      $input['moka']=$role['moka'];
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

    //上传相册图片
    public function albumupdate(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request,[
        'id'=>'required',
        'img'=>'required|Image',
      ]);
      $id = $request->input('id');
      $img = $request->file('img');
      $album = DB::table('Album')
        ->where('id',$id);
        if ($album->get()->count() == 0) {
          $result = $this->returnMsg('500',"error id");
          return response()->json($result);
        }
      $imgroot = 'mokaalbum'.$role['moka'].''.$id.''.md5(time()).".".$img->getClientOriginalExtension();
      $imgroot2 = 'mokaalbum'.$role['moka'].''.$id.''.md5(time())."s.".$img->getClientOriginalExtension();
      $new = public_path().'/'.$imgroot2;
      PhotoController::small($img,200,200,$new,$img->getClientOriginalExtension());
      // return $new;
      // QiniuController::deleteall('mokaalbum'.$role['moka'].''.$id);
      $sending = QiniuController::update($img,$imgroot);
      if ($sending == 500) {
        $result = $this->returnMsg('500',"upload failed");
        return response()->json($result);
      }
      $sending = QiniuController::update($new,$imgroot2);
      if ($sending == 500) {
        $result = $this->returnMsg('500',"upload failed");
        return response()->json($result);
      }
      unlink($new);

      $input['img_s'] = ''.$imgroot2;
      $input['img_l'] = ''.$imgroot;
      $input['img_snum'] = $imgroot2;
      $input['img_lnum'] = $imgroot;
      $input['mokaid'] = $id;
      $input['imgnum'] = 1;
      $input['act'] = 2;
      $input['moka']=$role['moka'];
      $album = DB::table('Album')->where('id',$id);
      $sum = $album->pluck('sum');
      if ($sum[0] == 0) {
        $album->update(['img'=>$input['img_s']]);
      }
      $result = Photo::create($input);
      $sum = DB::table('Photos')
        ->where('mokaid',$id)
        ->where('act',2)
        ->get()->count();
      $album ->update(['sum'=>$sum]);
      $result = $this->returnMsg('200',"ok",$input['img_s']);
      return response()->json($result);
    }

    //上传通告图片
    public function officeupdate(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request,[
        'id'=>'required',
        // 'img'=>'required|Image',
        'num'=>'required|Numeric',
      ]);
      $id = $request->input('id');
      $img = $request->file('img',null);
      $num = $request->input('num');
      if ($img == null) {
          $sending = QiniuController::deleteall('office'.$role['moka'].''.$id.''.$num);
          if ($sending == 500) {
            $result = $this->returnMsg('500',"deleted failed");
            return response()->json($result);
          }
          $result = $this->returnMsg('200',"deleted");
          return response()->json($result);
        }
      $imgroot = 'office'.$role['moka'].''.$id.$num.md5(time()).".".$img->getClientOriginalExtension();
      $imgroot2 = 'office'.$role['moka'].''.$id.$num.md5(time())."s.".$img->getClientOriginalExtension();
      $new = public_path().'/'.$imgroot2;
      PhotoController::small($img,200,200,$new,$img->getClientOriginalExtension());
      // return $new;
      QiniuController::deleteall('office'.$role['moka'].''.$id.''.$num);

      $sending = QiniuController::update($img,$imgroot);
      if ($sending == 500) {
        $result = $this->returnMsg('500',"upload failed");
        return response()->json($result);
      }
      $sending = QiniuController::update($new,$imgroot2);
      if ($sending == 500) {
        $result = $this->returnMsg('500',"upload failed");
        return response()->json($result);
      }
      unlink($new);
      $input['img_s'] = ''.$imgroot2;
      $input['img_l'] = ''.$imgroot;
      $input['img_snum'] = $imgroot2;
      $input['img_lnum'] = $imgroot;
      $input['mokaid'] = $id;
      $input['imgnum'] = $num;
      $input['ps'] = $request->input('ps','');
      $input['act'] = 3;
      $input['moka']=$role['moka'];
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

    public static function small($background, $width, $height,$newfile,$type) {
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
