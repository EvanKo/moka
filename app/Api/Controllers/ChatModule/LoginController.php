<?php

namespace App\Api\Controllers;
use Log;
use App\Api\Controllers\BaseController;
use Illuminate\Support\Facades\Session;
use Curl\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Wechat;
use JWTAuth;
use TopClient;
use ResultSet;
use DB;
use RequestCheckUtil;
use TopLogger;
use Illuminate\Support\Facades\Redis;
use AlibabaAliqinFcSmsNumSendRequest;

class LoginController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
  /**
   *@author Arius
   *@function to get wechat code
   *
   *@return  redirect a url with the code
   */
    public function index(){
      $appid = "wx95eaefe010f7c7e8";
      $redirect_uri = urlencode("http://".$_SERVER['HTTP_HOST']."/api/code");
      //不弹窗取用户openid，snsapi_base;弹窗取用户openid及详细信息，snsapi_userinfo;
      $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect";
      return redirect($url);
    }
    /**
     *@author Arius
     *@function to get wechat users detail and store
     *@param wechatcode and remote ip
     *@return  user openid
     */
    public function info($code,$ip){
      $appid = "wx95eaefe010f7c7e8";
      $appsecret = "bf5f7646164df5cd99d8a0363b6b0ed6";
      $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
      $curl = new Curl();
      $curl->setOpt(CURLOPT_SSL_VERIFYPEER, FALSE);
      $curl->get($url);
      $response = $curl->response;
      $response = json_decode($response,true);
      $access_token = $response['access_token'];
      $openid = $response['openid'];
      $url_info ="https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
      $curl->get($url_info);
      $info = $curl->response;
      $info = json_decode($info,true);
      $info['lastip'] = $ip;
     Log::info('login information:'.$info['lastip']); 
      LoginController::store($info);
      $curl->close();
      return $openid;
    }

	public function jsConfig(Request $request)
  {
		$appid = "wx95eaefe010f7c7e8";
   		$appsecret = "bf5f7646164df5cd99d8a0363b6b0ed6";
		$access_token = $this->getAccessToken($appid,$appsecret);
		$ticket = $this->getJsTicket($access_token);

		$noncestr= $this->getNonceStr();
		$timestamp = time();
		$url = $request->get('url');

		$inputObj['jsapi_ticket'] = $ticket;
		$inputObj['noncestr'] = $noncestr;
		$inputObj['timestamp'] = $timestamp;
		$inputObj['url'] = $url;
		//按照键值从小到大排序,并用url键值对格式拼接字符串
		ksort($inputObj);		
		$buff = "";
    	foreach ($inputObj as $k => $v)
       	{
         	if($k != "sign" && $v != "" && !is_array($v)){
             	 $buff .= $k . "=" . $v . "&";
           	}
        }
        $string = trim($buff, "&");
		//sha1签名
		$signature = sha1($string);

		$data['appid'] = $appid;
		$data['timestamp'] = $timestamp;
		$data['noncestr'] = $noncestr;
		$data['signature'] =$signature;
		return $this->returnMsg('200','ok',$data);
   
   }

	public function getAccessToken($appid,$appsecret)
 {
		$access_token = Redis::GET('appAccessToken');	
		if(!$access_token){
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			$output = curl_exec($curl);
			curl_close($curl);
			$data = json_decode($output,TRUE);
			$access_token = $data['access_token'];
			Redis::SET('appAccessToken',$access_token);
			Redis::EXPIRE('appAccessToken',7200);//微信access_token有效时间7200秒
		}
		return $access_token;
	}

	public function getJsTicket($access_token)
  {
		$jsTicket = Redis::get('jsTicket');
		if(!$jsTicket){
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi";
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			$output = curl_exec($curl);
			curl_close($curl);
			$data = json_decode($output,TRUE);
			$ticket = $data['ticket'];
			Redis::SET('jsTicket',$ticket);
			Redis::EXPIRE('jsTicket',7200);//微信ticket有效时间为7200秒
		}
		return $jsTicket;
}

	  public function getNonceStr($length = 32) 
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {  
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
        } 
        return $str;
    }

    /**
     *@author Arius
     *@function store wechat usr
     *@param array of user information
     *
     */
    public function store($info){
      $openid = $info['openid'];
      $user = wechat::where("openid","=",$openid)->first();
	//Log::info('user data:'.$user);
      if ($user == null) {
        Wechat::create($info);
      }
      else {
	$data['lastip'] = $info['lastip'];
	Log::info('data_lastip:'.$data['lastip']);
        $user->update($data);
//	return $this->returnMsg('200','You already have an acount');
      }
      $result = $this->returnMsg('200');
      return response()->json($result);
    }
    /**
     *@author Arius
     *@function set a session for tel number checking
     *@param tel
     *@return sms sending status
     */
    public function sessionSet(Request $request){
      $time = strtotime(date('Y-m-d H:i:s',time()));//integer
      $time = $time%10000;
		if(strlen($time)== 3)
			$time = "1$time";
	$tel = $request->input('tel');
      $value = array ('lastip'=>$_SERVER['REMOTE_ADDR'],'tel'=>$request['tel']);
    
     if($value['tel']==''){
            $result = $this->returnMsg('500','no params tel',$result);
            return response()->json($result);
      }

      $num = 'k'.strval($time);
	$value['num'] = $num;
    //  $request->session()->put($num, $value);
	$check = Redis::hmset($num,$value);Redis::expire($num,900);//改用redis存验证码，key存活时间900s 修改者：古毅
      // Session::flush();
      // $data = Session::all();
      // return $data;
      $result=LoginController::message($time,$request['tel']);
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }

    /**
     *@author Arius
     *@function send sms combined with Alidayu
     *@param checking code ,sms number
     *
     *@todo  ip update for Alidayu white list
     */
    public function message($num,$tel){
    	$c = new TopClient();
    	$c->appkey = "23549086";  //  App Key的值 这个在开发者控制台的应用管理点击你添加过的应用就有了
    	$c->secretKey = "1cc1d6ac4c6be2810c56e8841956ca56"; //App Secret的值也是在哪里一起的 你点击查看就有了
    	$req = new AlibabaAliqinFcSmsNumSendRequest();
    	$req->setExtend(""); //这个是用户名记录那个用户操作
    	$req->setSmsType("normal"); //这个不用改你短信的话就默认这个就好了
    	$req->setSmsFreeSignName("递一快"); //这个是签名
    	$req->setSmsParam("{'code':'".$num."'}"); //这个是短信签名
    	$req->setRecNum($tel); //这个是写手机号码
    	$req->setSmsTemplateCode("SMS_62320148"); //这个是模版ID 主要也是短信内容
        $resp = $c->execute($req);
        $resp = json_encode($resp);
        $resp = json_decode($resp);
        if(isset($resp->result)){
            if($resp->result->err_code == 0){
                $result = $this->returnMsg('200','OK');
    	        return response()->json($result);
            }
        }
            $result = $this->returnMsg('52001',$resp);
    	    return response()->json($result);
    }
    /**
     *@author Arius
     *@function check code by session and set tel
     *@param checking code
     *
     *
     */
    public function check(Request $request){
      $num = $request->input('num');
      $invite = $request->input('invite',null);
      $num = 'k'.strval($num);
      $usr = JWTAuth::toUser();
      $ip = $usr['lastip'];			Log::info('now:'.$ip);
 //     $value = $request->session()->get($num, 'default');
	$value = Redis::hgetall($num);
if($value){
	$n = $value['num'];			Log::info('lastip:'.$value['lastip']);
	$tel = $value['tel'];			Log::info($tel);
      if ($n == $num) {
        if ($value['lastip']==$ip) {
          $usr['tel']=$value['tel'];
          $input=array();
          $input['openid']=$usr['openid'];
          $input['tel']=$usr['tel'];
          $input['invited']=$invite;
	  $input['lastip']=$ip;
          DB::table('wechats')
		->where('openid',$input['openid'])
		->update($input);
      //    $request->session()->forget($num);
          JWTAuth::refresh();
          $usr['now'] = time();
          $usr['secret'] = "wearevtmers";
          $usr['random'] = rand(1000000,10000000);
          // return $usr;
          $token = JWTAuth::fromUser($usr);	
	  $result = $this->returnMsg('200','ok',$token);
	  //验证成果后删除redis验证码
          Redis::del($num);
          return response()->json($result);
							Log::info('Login check');
        }					Log::info('wrong ip');
        $result = $this->returnMsg('52002','ERROR CODE,ip not same');
        return response()->json($result);
      }						Log::info('wrong code');
        $result = $this->returnMsg('52002','ERROR CODE,code not same'.$tel);
        return response()->json($result);
    }else{
	return $this->returnMsg('52002','验证码过期');
	}
}
    /**
     *@author Arius
     *@function wechat code test
     *
     *
     *
     */
    public function code($request){
      $arr = array ('code'=>$_GET['code']);
      return response()->json(compact('arr'));
    }


    public function test(){
      $curl = new Curl();//测试Curl
      // $curl->get('www.obstacle.cn:7007/api/works');
      // $response = $curl->response;
      // $response = json_encode($response,true);
      // $response = json_decode($response,true);
      return JWTAuth::toUser();
      $arr = array ('status'=>"success");
      return response()->json(compact('arr'));
    }
}
