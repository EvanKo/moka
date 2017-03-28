<?php

namespace App\Api\Controllers;
use File;
use App\Api\Controllers\BaseController;
use Illuminate\Support\Facades\Session;
use Curl\Curl;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Role;
use App\Property;
use JWTAuth;
use TopClient;
use ResultSet;
use RequestCheckUtil;
use TopLogger;
use AlibabaAliqinFcSmsNumSendRequest;

class LoginController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    //内部判断是已存在手机号
    protected function phonecheck($tel){
      if($last = Role::where('tel','=',$tel)->first()){
        return false;
      }
      return true;
    }
    //注册
    public function register(Request $request){
      $tel = $request->input('tel',null);
      $name = $request->input('name',null);
      $sex = $request->input('sex',null);
      $password = $request->input('password',null);
      if ($tel == null || $password == null || $name == null || $sex == null) {
          $result = $this->returnMsg('500',"INFORMATION ERROR");
          return response()->json($result);
      }
      if (LoginController::phonecheck($tel)) {
          $num = time()%100000;
          $num = rand(1,9)*100000 + $num;
          $input['tel']=$tel;
          $input['password']=sha1($password);
          $input['moka'] = $num;
          $input['name'] = $name;
          $input['sex'] = $sex;
          $input['head'] = $_SERVER['HTTP_HOST'].'/photo/head/timg.jpeg';
          $input['lastest'] = date('y-m-d',time());
          $result = Role::create($input);
          $result = Property::create($input);
          $result = $this->returnMsg('200',"ok",$result);
          return response()->json($result);
      }
      $result = $this->returnMsg('500',"TEL HAVE EXISTED");
      return response()->json($result);
    }
    //登陆
    public function login(Request $request){
      $tel = $request->input('tel',null);
      $password = $request->input('password',null);
      if ($tel==null||$password==null) {
          $result = $this->returnMsg('500',"TEL OR PASSWORD ERROR");
          return response()->json($result);
      }
      if($last = Role::where('tel','=',$tel)->first()) {
        if (sha1($password) == $last['password']) {
          if (!strpos($last['lastest'],date('y-m-d',time()))) {
            $object = DB::table('Roles')
                      ->where('id', $last['id']);
            $object->update(['login' => $last['login']+1]);
            $object->update(['lastest' => date('y-m-d',time())]);
          }
          $data = DB::table('Roles')
          ->where('tel',$tel)
          ->select('id','name','province','city','sex')
          ->get();
          $last['now'] = time();
          $last['secret'] = "wearevtmers";
          $last['random'] = rand(1000000,10000000);
          $result['token'] = JWTAuth::fromUser($last);
          $result['information'] = $data;
          $result = $this->returnMsg('200','ok',$result);
          return response()->json($result);
        }
        $result = $this->returnMsg('500',"TEL OR PASSWORD ERROR");
        return response()->json($result);
      }
      $result = $this->returnMsg('500',"TEL NOT EXISTED");
      return response()->json($result);
    }
    //上传资料
    public function update(){

    }
    //上传头像
    public function headUpdate(Request $request){
      $root = JWTAuth::toUser();
      $id = $root['id'];
      $moka = $root['moka'];
      $file = $request->file('head',null);
      if ($file == null) {
        $result = $this->returnMsg('500',"IMG NOT UPLOAD");
        return response()->json($result);
      }
      File::delete(public_path().'/photo/head/'.$moka.'.jpg');
      File::delete(public_path().'/photo/head/'.$moka.'.jpeg');
      File::delete(public_path().'/photo/head/'.$moka.'.png');
      $file->move( public_path().'/photo/head/',$moka.".".$file->getClientOriginalExtension());
      $object = Role::find($id);
      $input['head'] = $_SERVER['HTTP_HOST']."/photo/head/".$moka.".".$file->getClientOriginalExtension();
      $result = $object->update($input);
      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }
    //选择角色
    public function roleUpdate(Request $request){
      $root = JWTAuth::toUser();
      $id = $root['id'];
      $role = $request->input('role',null);
      if ($role==null||($role<1||$role>4)) {
          $result = $this->returnMsg('500',"error role ");
          return response()->json($result);
      }
      $object = Role::find($id);
      if (isset($object['role'])) {
        $result = $this->returnMsg('500',"error role ");
        return response()->json($result);
      }
      $input['role'] = $role;
      $result = $object->update($input);
      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }
    //查询
    public function checkmanager(){
      return JWTAuth::toUser();
    }
    //认证
    public function auth(){

    }
    //忘记密码
    public function forget(){

    }
    //短信
    public function sess(){

    }
}
