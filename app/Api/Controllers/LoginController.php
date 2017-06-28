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
use App\Figure;
use App\Friend;
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
      $num = $request->input('num',null);
      $password = $request->input('password',null);
      if ($tel == null || $password == null || $name == null || $sex == null||$num ==null) {
          $result = $this->returnMsg('500',"INFORMATION ERROR");
          return response()->json($result);
      }
      $num = 'k'.strval($num);
      $value = Session::get($num, 'default');
      if ($value != 'default') {
        if ($value['tel'] == $tel) {
          // return $input;
          Session::forget($num);
          if (LoginController::phonecheck($tel)) {
              $mokanum = time()%100000;
              $mokanum = rand(1,9)*100000 + $mokanum;
              $input['tel'] = $tel;
              $input['password']=sha1($password);
              $input['moka'] = $mokanum;
              $input['frienda'] = $mokanum;
              $input['friendb'] = $mokanum;
              $input['name'] = $name;
              $input['sex'] = $sex;
              $input['head'] = 'head/timg.jpeg';
              $input['lastest'] = date('y-m-d',time());
              $result = Role::create($input);
              // $token = JWTAuth::fromUser($input);
              $result = Property::create($input);
              $result = Friend::create($input);
              $result = $this->returnMsg('200',"ok");
              return response()->json($result);
          }
          $result = $this->returnMsg('500',"TEL HAVE EXISTED");
          return response()->json($result);
        }
        $result = $this->returnMsg('500','tel error');
        return response()->json($result);
      }
      $result = $this->returnMsg('500','num error');
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
          ->select('id','name','province','city','sex','head','v','moka','role')
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
    public function update(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request, [
        'name' => 'required',
        'area' => 'required|Numeric',
        'province' => 'required',
        'city' => 'required',
        'office' => 'required',
        'intro' => 'required',
        'workexp' => 'required',
      ]);
      $result = DB::table('Roles')
        ->where('id',$role['id'])
        ->update($request->all());
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }
    //上传头像
    public function bgUpdate(Request $request){
      $root = JWTAuth::toUser();
      $id = $root['id'];
      $moka = $root['moka'];
      $file = $request->file('img',null);
      if ($file == null) {
        $result = $this->returnMsg('500',"IMG NOT UPLOAD");
        return response()->json($result);
      }
      $end = 'bg/'.$moka.".".$file->getClientOriginalExtension();
      QiniuController::update($file,$end);
      $object = Role::find($id);
      $input['bgimg'] = ''.$end;
      $result = $object->update($input);
      $result = $this->returnMsg('200',"ok",$input['bgimg']);
      return response()->json($result);
    }
    //上传头像
    public function headUpdate(Request $request){
      $root = JWTAuth::toUser();
      $id = $root['id'];
      $moka = $root['moka'];
      $file = $request->file('img',null);
      if ($file == null) {
        $result = $this->returnMsg('500',"IMG NOT UPLOAD");
        return response()->json($result);
      }
      $end = 'mokahead/'.$moka.".".$file->getClientOriginalExtension();
      QiniuController::update($file,$end);
      $object = Role::find($id);
      $input['head'] = ''.$end;
      $result = $object->update($input);
      $result = $this->returnMsg('200',"ok",$input['head']);
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
    //登出
    public function logout(){
      JWTAuth::refresh();
      $result = $this->returnMsg('200',"logouted");
      return response()->json($result);
    }
    //查询
    public function checkmanager(){
      $result = JWTAuth::toUser();
      $result = $this->returnMsg('200',"ok",$result);
      return response()->json($result);
    }


    //忘记密码
    public function forget(Request $request){
      $tel = $request->input('tel',null);
      $num = $request->input('num',null);
      $password = $request->input('password',null);
      if ($tel == null || $password == null ||$num ==null) {
          $result = $this->returnMsg('500',"INFORMATION ERROR");
          return response()->json($result);
      }
      $num = 'k'.strval($num);
      $value = Session::get($num, 'default');
      if ($value != 'default') {
        if ($value['tel'] == $tel) {
          // return $input;
          Session::forget($num);
          $input['password']=sha1($password);
          $result = DB::table('Roles')->where('tel',$tel)
            ->update($input);
          $result = $this->returnMsg('200',"ok",$result);
          return response()->json($result);
        }
        $result = $this->returnMsg('500','num error');
        return response()->json($result);
      }
      $result = $this->returnMsg('500','num error');
      return response()->json($result);
    }
    //忘记密码
    public function changepassword(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request, [
        'old' => 'required',
        'new' => 'required',
      ]);
      $old = $request->input('old');
      $new = $request->input('new');
      if ($role['password'] == sha1($old)) {
        DB::table('Roles')->where('id',$role['id'])
          ->update(['password'=>sha1($new)]);
        $result = $this->returnMsg('200','new password:'.$new,$new);
        return response()->json($result);
      }
      $result = $this->returnMsg('500','old password error');
      return response()->json($result);
    }
    //短信
    public function message($num,$tel){
    	$c = new TopClient();
    	$c->appkey = "23553742";  //  App Key的值 这个在开发者控制台的应用管理点击你添加过的应用就有了
    	$c->secretKey = "170f0500f220c2b61a95c2e9065a6670"; //App Secret的值也是在哪里一起的 你点击查看就有了
    	$req = new AlibabaAliqinFcSmsNumSendRequest();
    	$req->setExtend(""); //这个是用户名记录那个用户操作
    	$req->setSmsType("normal"); //这个不用改你短信的话就默认这个就好了
    	$req->setSmsFreeSignName("滴达"); //这个是签名
    	$req->setSmsParam("{'code':'".$num."'}"); //这个是短信签名
    	$req->setRecNum($tel); //这个是写手机号码
    	$req->setSmsTemplateCode("SMS_32485128"); //这个是模版ID 主要也是短信内容
        $resp = $c->execute($req);
        $resp = json_encode($resp);
        $resp = json_decode($resp);
        if(isset($resp->result)){
            if($resp->result->err_code == 0){
    	        return 'ok';
            }
        }
    	    return $resp;
    }

    public function sessionSet(Request $request){
      $time = strtotime(date('Y-m-d H:i:s',time()));//integer
      $time = $time%100;
      $time = rand(1,9)*1000 + $time;
      $value = array ('lastip'=>$_SERVER['REMOTE_ADDR'],'tel'=>$request->input('tel'));
      $num = 'k'.strval($time);
      Session::put($num, $value);
      // Session::flush();
      // $data = Session::all();
      // return $data;
      $result=LoginController::message($time,$request['tel']);
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }
    public function check(Request $request){
      return session::all();
    }
    //搜索
    public function search(Request $request){
      $key = $request->input('key',null);
      $page = $request->input('page',1);
      if ($key == null) {
        $result = $this->returnMsg('500','request error');
        return response()->json($result);
      }
      if (is_numeric($key)&&mb_strlen($key,'gb2312') == 6){
        $result = DB::table('Roles')->where('moka',$key)
        ->select('moka','name','province','city','head','sex')
        ->skip(($page-1)*10)->limit(10)->get();
        if ($result->count() == 0) {
          $result = $this->returnMsg('200','not exited');
          return response()->json($result);
        }
        $result = $this->returnMsg('200','ok',$result);
        return response()->json($result);
      }
      $result = DB::table('Roles')->where('name','like',"%".$key."%")
      ->select('moka','name','province','city','head','sex')
      ->skip(($page-1)*10)->limit(10)->get();
      if ($result->count() == 0) {
        $result = $this->returnMsg('200','not exited');
        return response()->json($result);
      }
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }
    //搜索
    public function area(Request $request){
      $key = $request->input('key',null);
      $page = $request->input('page',1);
      if ($key == null) {
        $result = $this->returnMsg('500','request error');
        return response()->json($result);
      }
      $area = DB::table('Area')->where('name','like',"%".$key."%")
      ->pluck('sort');
      $result = DB::table('Roles')->where('area',$area)
      ->select('moka','name','province','city','head','sex')
      ->orderBy('id','desc')
      ->skip(($page-1)*10)->limit(10)->get();
      if ($result->count() == 0) {
        $result = $this->returnMsg('200','bottom');
        return response()->json($result);
      }
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }
    //模特身材
    public function body(Request $request){
      $role = JWTAuth::toUser();
      $model = $role['role'];
      if ($model != '1') {
        $result = $this->returnMsg('500','not model');
        return response()->json($result);
      }
      $isset = DB::table('Figures')->where('moka',$role['moka']);
      if ($isset->get()->count() == 0) {
        $this->validate($request, [
          'height' => 'required|Numeric',
          'weight' => 'required|Numeric',
          'hips' => 'required|Numeric',
          'bust' => 'required|Numeric',
          'waist' => 'required|Numeric',
          'shoe' => 'required|Numeric',
        ]);
        $data = $request->all();
        $data['moka'] = $role['moka'];
        $result = Figure::create($data);
        $result = $this->returnMsg('200','ok',$result);
        return response()->json($result);
      }
      $this->validate($request, [
        'height' => 'Numeric',
        'weight' => 'Numeric',
        'hips' => 'Numeric',
        'bust' => 'Numeric',
        'waist' => 'Numeric',
        'shoe' => 'Numeric',
      ]);
      $result = $isset
        ->update($request->all());
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);

    }
    //验证号码
    protected function phonechecks(Request $request){
      if(LoginController::phonecheck($request->input('tel'))){
        $result = $this->returnMsg('200','new tel');
        return response()->json($result);
      }
      $result = $this->returnMsg('500','tel EXISTED');
      return response()->json($result);
    }

}
