<?php

namespace App\Api\Controllers;

use App\Api\Controllers\PhotoController;
use App\Api\Controllers\QiniuController;
use App\Api\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Order;
use App\Record;
use JWTAuth;
use DB;

class OfficeController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }

    //开始编辑工作室
    public function start(Request $request){
      $role = JWTAuth::toUser();
      $object = DB::table('Orders')
        ->where('moka',$role['moka'])
        ->where('type',2)
        ->where('finish',0)
        ->get();
      if ($object->count() != 0) {
          $photo  = DB::table('Orders')
            ->where('moka',$role['moka'])
            ->where('finish',0)
            ->orderBy('id','desc')
            ->limit(1)
            ->pluck('id');
          $photos = DB::table('Photos')
            ->where('mokaid',$photo)
            ->where('act',3)
            ->orderBy('imgnum')
            ->select('id','Photos.imgnum','Photos.img_s','ps')
            ->get();
          $office = DB::table('Orders')
            ->where('moka',$role['moka'])
            ->where('finish',0)
            ->where('type',2)
            ->orderBy('id','desc')
            ->limit(1)
            ->get();
          $result['photos'] = $photos;
          $result['office'] = $office;
          $result = $this->returnMsg('200',"ok",$result);
          return response()->json($result);
        }
      $input['area'] = $role['area'];
      $input['moka'] = $role['moka'];
      $input['type'] = 2;
      // $input['local']=$role['province'].$role['city'];
      $input['img'] = 'head/timg.jpeg';
      $result = Order::create($input);
      $num = json_decode($result,true);
      $num = $num['id'];
      $result = $this->returnMsg('200',"office id:".$num,$num);
      return response()->json($result);
    }

    //删除工作室
    public function delete(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request,[
        'id'=>'required',
      ]);
      $id = $request->input('id');
      $office = DB::table('Orders')
        ->where('id',$id);
        $officemoka =$office->pluck('moka');
      if ($officemoka[0]!=$role['moka']) {
        $result = $this->returnMsg('500',"not yours");
        return response()->json($result);
      }
      $sending = QiniuController::deleteall('office'.$role['moka'].''.$id.'');
      if ($sending == 500) {
        $result = $this->returnMsg('500',"del fail");
        return response()->json($result);
      }
      DB::table('Photos')
        ->where('mokaid',$id)
        ->where('act',3)
        ->delete();
      // $root = public_path().'/photo/office/'.$mokaid.'/';
      // if(file_exists($root)){
      //   ActivityController::deldir($root);
      // }
      DB::table('Orders')
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
        // 'type'=>'required',
        'content'=>'required',
        'lasting'=>'required|Numeric',
        // 'reserved'=>'required|Date',
        'place'=>'required',
        'photonum'=>'required|Numeric',
        'focusphoto'=>'required|Numeric',
        'price'=>'required|Numeric',
      ]);
      $id = $request->input('id',null);
      $office = DB::table('Orders')->where('id',$id);
      $finish = $office->pluck('finish');
      if ($finish[0] == 1) {
        $result = $this->returnMsg('500','mokaoffice haved saved');
        return response()->json($result);
      }
      $moka = $office->pluck('moka');
      if ($moka[0] != $role['moka']) {
        $result = $this->returnMsg('500','error request');
        return response()->json($result);
      }
      $office->update($request->all());
      $office->update(['finish'=>1]);
      $input['target']=2;
      $input['target_id']=$request->input('id');
      $input['moka']=$role['moka'];
      $input['area']=$role['area'];
      Record::create($input);
      $result = $this->returnMsg('200','saved');
      return response()->json($result);

    }

    //详情
    public function office(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request,[
        'id'=>'required',
      ]);
      $id = $request->input('id');
    $photos = DB::table('Photos')
      ->where('mokaid',$id)
      ->where('act',3)
      ->orderBy('imgnum')
      ->select('id','Photos.imgnum','Photos.img_l','ps')
      ->get();
    $office = DB::table('Orders')
      ->where('id',$id)
      ->get();
    $result['photos'] = $photos;
    $result['office'] = $office;
    $result = $this->returnMsg('200',"ok",$result);
    return response()->json($result);
  }
  //详情
  public function cover(Request $request){
    $role = JWTAuth::toUser();
    $this->validate($request,[
      'id'=>'required',
      'img'=>'required|Image',
    ]);
    $id = $request->input('id');
    $img = $request->file('img');
    $office = DB::table('Orders')->where('id',$id);
    $moka = $office->pluck('moka');
    if ($moka[0] != $role['moka']) {
      $result = $this->returnMsg('500','error request');
      return response()->json($result);
    }
    $imgroot2 = 'officehead'.$role['moka'].''.$id.md5(time())."s.".$img->getClientOriginalExtension();
    $new = public_path().'/'.$imgroot2;
    PhotoController::small($img,200,200,$new,$img->getClientOriginalExtension());
    // return $new;
    QiniuController::deleteall('officeofficehead'.$role['moka'].''.$id.'');

    $sending = QiniuController::update($img,$imgroot2);
    if ($sending == 500) {
      $result = $this->returnMsg('500',"upload failed");
      return response()->json($result);
    }
    unlink($new);

    $office->update(['img'=>$imgroot2]);
    $result = $this->returnMsg('200',"ok",$imgroot2);
    return response()->json($result);

  }
//   //列表
//   public function officelist(Request $request){
//     $role = JWTAuth::toUser();
//     $this->validate($request,[
//       'id'=>'required',
//     ]);
//     $id = $request->input('id');
//   $photos = DB::table('Photos')
//     ->where('mokaid',$id)
//     ->where('act',3)
//     ->orderBy('imgnum')
//     ->select('id','Photos.imgnum','Photos.img_s')
//     ->get();
//   $office = DB::table('Orders')
//     ->where('id',$id)
//     ->get();
//   $result['photos'] = $photos;
//   $result['office'] = $office;
//   $result = $this->returnMsg('200',"ok",$result);
//   return response()->json($result);
// }
    //
    // protected static function deldir($dir) {
    //   //先删除目录下的文件：
    //   $dh=opendir($dir);
    //   while ($file=readdir($dh)) {
    //     if($file!="." && $file!="..") {
    //       $fullpath=$dir."/".$file;
    //       if(!is_dir($fullpath)) {
    //           unlink($fullpath);
    //       } else {
    //           deldir($fullpath);
    //       }
    //     }
    //   }
    //
    //   closedir($dh);
    //   //删除当前文件夹：
    //   if(rmdir($dir)) {
    //     return true;
    //   } else {
    //     return false;
    //   }
    // }

    //地区活动
    // public function areaoffice(Request $request){
    //     $role = JWTAuth::toUser();
    //     $area = $role['area'] == null ? 1:$role['area'];
    //     $page = $request->input('page',1);
    //     $area = $request->input('area',$area);
    //     $type = $request->input('type',null);
    //     $record = DB::table('Orders')
    //       ->where('area',$area)
    //       ->where('finish',1);
    //     if ($type != null) {
    //       $record = $record->where('type',$type);
    //     }
    //       $record = $record->orderBy('id','desc')
    //       // ->where('pass','1')
    //       ->skip(($page-1)*10)
    //       ->limit(10)
    //       ->select('img','area','type','view','id','title','price')
    //       ->get();
    //     if ($record->count() == 0) {
    //       $result = $this->returnMsg('200','bottum');
    //       return response()->json($result);
    //     }
    //     $result = $this->returnMsg('200','ok',$record);
    //     return response()->json($result);
    // }
}
