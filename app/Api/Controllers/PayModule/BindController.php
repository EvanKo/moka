<?php

namespace App\Api\Controllers\PayModule;

use Log;
use App\Wechat;
use App\Api\Controllers\BaseController;
use Illuminate\Support\Facades\Session;
use Curl\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use JWTAuth;
use DB;
use Illuminate\Support\Facades\Redis;

class BindController extends BaseController
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
      return $url;
    }
    /**
     *@author Arius
     *@function to get wechat users detail and store
     *@param wechatcode and remote ip
     *@return  user openid
     */
    public function info($code){
	   	$appid = "wx95eaefe010f7c7e8";
		$appsecret = "bf5f7646164df5cd99d8a0363b6b0ed6";
		$userInfo = JWTAuth::fromUser();
		$mokaid = $userInfo['userInfo'];

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
      	$curl->close();
		$info = json_decode($info,true);
		$info['mokaid'] = $mokaid;
		$result = $this->store($info);
		if($result)
			return $this->returnMsg('200','ok');
		else 
			return $this->returnMsg('500','save fail');
	}

	public function store($info){
   		$openid = $info['openid'];
    	$user = wechat::where("openid","=",$openid)->first();
      	if ($user == null) {
       		$query = Wechat::create($info);
     	}else {
        	$query = $user->update($data);
		}
		if($query)
      		return True;
		else 
			return False;
    }
/*
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
 */
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
