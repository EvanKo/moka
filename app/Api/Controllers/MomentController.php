<?php

namespace App\Api\Controllers;

use File;
use DB;
use App\Api\Controllers\BaseController;
use App\Api\Controllers\AppreciateController;
use App\Api\Controllers\CommentController;
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
	  $root = public_path().'/photo/moment/';
	  if(!file_exists($root)){
	  	mkdir($root);
	  }
	  $root = $root.$moka.'/';
      $root2 = '/photo/moment/'.$moka.'/';
      if(!file_exists($root)){
        mkdir($root);
      }
      $num = md5(time()).".".$img->getClientOriginalExtension();
      $img->move( $root,$num);
      $input['img'] = $_SERVER['HTTP_HOST'].$root2.$num;
      $input['imgnum'] = $num;
      $input['content'] = $content;
      $input['moka'] = $moka;
      $input['area'] = $role['area'];
      $result = Moment::create($input);
      $result = json_decode($result,true);
      $input['target_id'] = $result['id'];
      $input['target'] = 1;
      $result = Record::create($input);
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
      $first = $record = DB::table('Moments')
        ->where('id',$id)
        ->where('moka',$role['moka']);
      $object = $first->get();
      if ($object->count() == 0) {
        $result = $this->returnMsg('500',"momentid error");
        return response()->json($result);
      }
      $record = DB::table('Records')
        ->where('target_id',$id)
        ->where('target',1)
        ->delete();
      $object = json_decode($object,true);
      $object = $object[0];
      $moka = $object['moka'];
      File::delete(public_path().'/photo/moment/'.$moka.'/'.$object['imgnum']);
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
