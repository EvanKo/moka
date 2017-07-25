<?php

namespace App\Api\Controllers;

use File;
use DB;
use App\Api\Controllers\BaseController;
use App\Api\Controllers\AppreciateController;
use App\Api\Controllers\CommentController;
use App\Api\Controllers\QiniuController;
use Illuminate\Support\Facades\Session;
use Curl\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Moment;
use App\Record;
use JWTAuth;

class MomentController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    //发动态
    public function make(Request $request){
      $role = JWTAuth::toUser();
      $moka = $role['moka'];
      $img = $request->file('img',null);
      $content = $request->input('content',null);
      if ($img == null) {
        $result = $this->returnMsg('500',"IMG NOT UPLOAD");
        return response()->json($result);
      }
      $num = md5(time()).".".$img->getClientOriginalExtension();

      $end = 'moment'.$role['moka'].$num;

      $sending = QiniuController::update($img,$end);
      if ($sending == 500) {
        $result = $this->returnMsg('500',"upload failed");
        return response()->json($result);
      }
      $input = $request->all();
      $input['img'] = ''.$end;

      $input['imgnum'] = $num;
      $input['content'] = $content;
      $input['moka'] = $moka;
      $input['area'] = $role['area'];
      $result = Moment::create($input);
      $result = json_decode($result,true);
      $input['target_id'] = $result['id'];
      $input['target'] = 1;
      $result1 = Record::create($input);
      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }
    //删除动态
    public function delete(Request $request){
      $role = JWTAuth::toUser();
      $id = $request->input('momentid',null);
      if ($id == null) {
        $result = $this->returnMsg('500',"momentid require");
        return response()->json($result);
      }
      $first = DB::table('Moments')
        ->where('id',$id);
        // ->where('moka',$role['moka']);
      $object = $first->get();
      if ($object->count() == 0) {
        $result = $this->returnMsg('500',"momentid error");
        return response()->json($result);
      }
      $object = json_decode($object,true);
      $object = $object[0];
      $moka = $object['moka'];
      if ($moka != $role['moka']) {
        $result = $this->returnMsg('500',"not your moment");
        return response()->json($result);
      }
      $record = DB::table('Records')
        ->where('target_id',$id)
        ->where('target',1)
        ->delete();
      $img = $object['img'];
      QiniuController::deleteall($img);
      AppreciateController::deleall(1,$object['id']);
      CommentController::deleall(1,$object['id']);
      $result =  $first->delete();
      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }
    //个人动态
    public function my(){

    }

    //test
    public function test(Request $request){
      $id = JWTAuth::toUser();
      $id = $id['moka'];
      $result = MomentController::self($id);
      return $result;
    }
    //增加浏览量
    protected function view(){

    }
}
