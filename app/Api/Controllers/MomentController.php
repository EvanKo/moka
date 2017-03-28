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
use App\Role;
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
      $root = public_path().'/photo/moment/'.$moka.'/';
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
      $object = Moment::find($id);
      if (!$object) {
        $result = $this->returnMsg('500',"momentid error");
        return response()->json($result);
      }
      $moka = $object['moka'];
      File::delete(public_path().'/photo/moment/'.$moka.'/'.$object['imgnum']);
      AppreciateController::deleall(1,$object['id']);
      CommentController::deleall(1,$object['id']);
      $result =  $object->delete();
      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }
    //个人动态
    public function my(){

    }
    public static function self($id){
      $role = DB::table('Roles')->where('moka',$id)
        ->select('id','moka', 'name','head','sex','role'
        ,'province','city')
        ->get();
      return $role;
    }
    //动态详情
    public function moment(Request $request){
      $id = $request->get('momentid',null);
      if ($id == null) {
        $result = $this->returnMsg('500','request error');
        return response()->json($result);
      }
      $moment = DB::table('Moments')->where('id',$id)
        ->select('id','moka','content','img','view','created_at');
      if (!$moment) {
        $result = $this->returnMsg('500','id error');
        return response()->json($result);
      }
      // $moment = json_decode($moment,true);
      // $moment = $moment[0];
      $data = $moment->get();
      $data = json_decode($data,true);
      $data = $data[0];
      $data['view'] += 1;
      $moment->update(['view'=>$data['view']]);
      $zan = AppreciateController::list(1,$id,10);
      $result['zan'] = $zan;
      $result['moment'] = $data;
      $result['author'] = MomentController::self($data['moka']);
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
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
