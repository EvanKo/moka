<?php
namespace App\Api\Controllers\PayModule;

use Log;
use App\Api\Controllers\BaseController;
use App\Wechat;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;


class WechatPayController extends BaseController
{
    /**
     * The authentication guard that should be used.
     *
     * @var string
     */
    public function __construct()
    {
        parent::__construct();

    }
	//购买普通会员回调函数
    public function normalmembernotify(Request $request){
		$msg = array();
	    $postStr = file_get_contents('php://input');
	    $msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
	    Log::info('msg:'.$msg['result_code']);
	    if($msg['result_code']=='SUCCESS'){
			//业务逻辑
			$openid = $msg['openid'];
			DB::beginTransaction();
			$orderinfo = DB::table('PayRecords')->where(['openid'=>$openid,'type'=>1,'status'=>0])
						->first();
			DB::table('Roles')->where('moka','=',$orderinfo->moka)->update(['level'=>1]);
			//DB::table('PayRecords')->where('openid','=',$openid)->update(['status'=>1]);
			$orderinfo->update(['status'=>1]);
			DB::commit();
			Log::info('user:'.$openid.' pay '.$msg['total_fee'].'.time:'.$msg['time_end']);
		}else{
			Log::warning('user'.$openid.' fail to pay');
		}
	}
	//购买高级会员回调函数
    public function advancemembernotify(Request $request){
		$msg = array();
	    $postStr = file_get_contents('php://input');
	    $msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
	    Log::info('msg:'.$msg['result_code']);
	    if($msg['result_code']=='SUCCESS'){
			//业务逻辑
			$openid = $msg['openid'];
			DB::beginTransaction();
			$orderinfo = DB::table('PayRecords')->where(['openid'=>$openid,'type'=>2,'status'=>0])
						->first();
			DB::table('Roles')->where('moka','=',$orderinfo->moka)->update(['level'=>2]);
			$orderinfo->update(['status'=>1]);
			DB::commit();
			Log::info('user:'.$openid.' pay '.$msg['total_fee'].'.time:'.$msg['time_end']);
		}else{
			Log::warning('user'.$openid.' fail to pay');
		}
	}	
	//购买至尊会员回调函数
    public function supermemembernotify(Request $request){
		$msg = array();
	    $postStr = file_get_contents('php://input');
	    $msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
	    Log::info('msg:'.$msg['result_code']);
	    if($msg['result_code']=='SUCCESS'){
			//业务逻辑
			$openid = $msg['openid'];
			DB::beginTransaction();
			$orderinfo = DB::table('PayRecords')->where(['openid'=>$openid,'type'=>3,'status'=>0])
						->first();
			DB::table('Roles')->where('moka','=',$orderinfo->moka)->update(['level'=>3]);
			$orderinfo->update(['status'=>1]);
			DB::commit();
			Log::info('user:'.$openid.' pay '.$msg['total_fee'].'.time:'.$msg['time_end']);
		}else{
			Log::warning('user'.$openid.' fail to pay');
		}
	}	
	//支付订单回调函数
    public function ordernotify(Request $request){
		$msg = array();
	    $postStr = file_get_contents('php://input');
	    $msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
	    Log::info('msg:'.$msg['result_code']);
	    if($msg['result_code']=='SUCCESS'){
			//业务逻辑
			$openid = $msg['openid'];
			DB::beginTransaction();
			$orderinfo = DB::table('PayRecords')->where(['openid'=>$openid,'type'=>4,'status'=>0])
						->first();
			$touser = DB::table('Roles')->where('moka','=',$orderinfo->tomoka)->first();
			$money = $touser->money;
			$touser->update(['money'=>$money+$orderinfo->amount]);
			$orderinfo->update(['status'=>1]);
			DB::commit();
			Log::info('user:'.$openid.' pay '.$msg['total_fee'].'.time:'.$msg['time_end']);
		}else{
			Log::warning('user'.$openid.' fail to pay');
		}
	}	
	//支付约拍回调函数
    public function dateOrderNotify(Request $request){
		$msg = array();
	    $postStr = file_get_contents('php://input');
	    $msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
	    Log::info('msg:'.$msg['result_code']);
	    if($msg['result_code']=='SUCCESS'){
			//业务逻辑
			$openid = $msg['openid'];
			//获取订单号
			$orderNum = Redis::get('payorder'.$msg['out_trade_no']);
			$amount = $msg['total_fee'];
			DB::beginTransaction();
			$userInfo = DB::table('wechats')->where('openid','=',$openid)->first();
			$mokaid = $userInfo->mokaid;
			$orderInfo = DB::table('Status')->where(['customer'=>$mokaid,'target_id'=>$orderNum,'status'=>1])->first();
			$orderInfo->update(['status'=>2]);	
			DB::commit();
			Log::info('user:'.$openid.' pay '.$msg['total_fee'].'.time:'.$msg['time_end']);
			return 'SUCCESS';
		}else{
			Log::warning('user'.$openid.' fail to pay');
			return "FAIL";
		}
	}
	
    public function unifiedOrder(Request $request)
	{	//从token获取用户信息
		$token = JWTAuth::getToken();
        $user_json = JWTAuth::toUser($token);
		$user = json_decode($user_json, true);
		$check_bind = $this->checkBindWechat($user['moka']);
		if(!$check_bind){
			return $this->returnMsg('500','Not bind the wechat account');
		}		
		$type = $request->input('type');
		$orderNum = $request->input('orderNum');

        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $server_url = "121.40.220.52";    

        $inputObj=array();
        $inputObj['appid'] = "wxa99e4ef76debee57";//微信支付分配的公众账号ID
        $inputObj['mch_id'] = "1462118902";//微信支付分配的商户号
        $inputObj['nonce_str'] = $this->getNonceStr();//随机字符串，长度要求在32位以内
        $inputObj['body'] = "moka`订单";//商品简单描述，该字段请按照规范传递
        $inputObj['out_trade_no'] = "1462118902".date("YmdHis").rand(111,999);//商户系统内部订单号，要求32个字符内、且在同一个商户号下唯一
        $inputObj['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];//APP和网页支付提交用户端ip

			//约定1为购买普通会员，2为高级会员,3为至尊会员,4为支付订单
		switch($type){
			case 1:
				$inputObj['notify_url']=$server_url."/api/nomalMemberNotify";//异步接收微信支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数
				$orderInfo = DB::table('PayRecords')->where(['moka'=>$user['moka'],'type'=>1,'status'=>0])->first();
				break;
			case 2:
				$inputObj['notify_url']=$server_url."/api/advanceMembernotify";//异步接收微信支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数
				$orderInfo = DB::table('PayRecords')->where(['moka'=>$user['moka'],'type'=>2,'status'=>0])->first();
				break;
			case 3:
				$inputObj['notify_url']=$server_url."/api/supermeMembernotify";//异步接收微信支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数
				$orderInfo = DB::table('PayRecords')->where(['moka'=>$user['moka'],'type'=>3,'status'=>0])->first();
				break;
			case 4:
				$inputObj['notify_url']=$server_url."/api/ordernotify";//异步接收微信支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数
				$orderInfo = DB::table('PayRecords')->where(['moka'=>$user['moka'],'type'=>4,'status'=>0])->first();
				break;
			case 5:
				$inputObj['notify_url']=$server_url."/api/dateordernotify";//异步接收微信支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数
				$orderInfo = DB::table('Orders')->where('id','=',$orderNum)->first();
				if(!$orderInfo){
					return $this->returnMsg('404','Not find the order');
				}
				$userInfo = DB::table('wechats')->where('mokaid','=',$user['moka'])->first();
				break;

		}
		$testopenid = "oSZHCuF-2t39bKrpcKztMQZqnrGc";
        $inputObj['trade_type']="JSAPI";//取值如下：JSAPI，NATIVE，APP等。公众号支付未JSAPI
        

        $inputObj['openid'] = $userInfo->openid;//$user['openid'];//公众号支付，此参数必传，此参数为微信用户在商户对应appid下的唯一标识
		$amount = 100*$orderInfo->amount;
	//$amount=100; //付款多少( /分）
		$inputObj['total_fee']=$amount;//订单总金额，单位为分

		Redis::set('payorder:'.$inputObj['out_trade_no'],$orderNum);//便于回调时找到该订单
        
        //签名
        //签名步骤一：按字典序排序参数
        ksort($inputObj);
        $buff = "";
        foreach ($inputObj as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $string = trim($buff, "&");
        //签名步骤二：在string后加入KEY
        $string = $string. "&key="."mokabangbangtang2017051915161234";
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);

        $inputObj['sign']=$result;//通过签名算法计算得出的签名值
		
		$order_flag = $inputObj['out_trade_no'];
        $xml="<xml>
               <appid><![CDATA[".$inputObj['appid']."]]></appid>
               <body><![CDATA[".$inputObj['body']."]]></body>
               <mch_id><![CDATA[".$inputObj['mch_id']."]]></mch_id>
               <nonce_str><![CDATA[".$inputObj['nonce_str']."]]></nonce_str>
               <notify_url><![CDATA[".$inputObj['notify_url']."]]></notify_url>
               <openid><![CDATA[".$inputObj['openid']."]]></openid>
               <out_trade_no><![CDATA[".$inputObj['out_trade_no']."]]></out_trade_no>
               <spbill_create_ip><![CDATA[".$inputObj['spbill_create_ip']."]]></spbill_create_ip>
               <total_fee><![CDATA[".$inputObj['total_fee']."]]></total_fee>
               <trade_type><![CDATA[".$inputObj['trade_type']."]]></trade_type>
               <sign><![CDATA[".$inputObj['sign']."]]></sign>
            </xml>";
	Log::info('xml------>'.$xml);
        $data=$this->postXmlCurl($xml,$url);
        if($data){  
            $rsxml = simplexml_load_string($data);
            if($rsxml->return_code == 'SUCCESS' and ((string)$rsxml->result_code)=='SUCCESS'){
                //支付签名
                //签名步骤一：按字典序排序参数
 		log::info($data);
                $payObj=array();
                $time=time();
                $payObj['appId']=$inputObj['appid'];
                $payObj['timeStamp']='"'.$time.'"';
                $payObj['nonceStr']=$this->getNonceStr();
                $payObj['package']="prepay_id=".$rsxml->prepay_id;
                $payObj['signType']="MD5";

                ksort($payObj);
                $buff = "";
                foreach ($payObj as $k => $v)
                {
                    if($k != "sign" && $v != "" && !is_array($v)){
                        $buff .= $k . "=" . $v . "&";
                    }
                }
                $string = trim($buff, "&");
                //签名步骤二：在string后加入KEY
                $string = $string. "&key="."moakbangbangtang2017051915161234";
                //签名步骤三：MD5加密
                $string = md5($string);
                //签名步骤四：所有字符转为大写
                $result = strtoupper($string);
                $data=array(
                    'appId'=>$payObj['appId'],
                    'timeStamp'=>$payObj['timeStamp'],
                    'nonceStr'=>$payObj['nonceStr'],
                    'package'=>$payObj['package'],
                    "signType"=>$payObj['signType'],
                    'paySign'=>$result
                );
                $result = $this->result('200', 'OK');
                $result['data']=$data;
                return response()->json($result);
            }else{
                $result = $this->result('204', $rsxml->return_msg);
                return response()->json($result); 
            }
            
        }else{ 
            $result = $this->result('204','ERROR');
            return response()->json($result); 
        }
    }
    public function postXmlCurl($xml, $url,$second = 30)
    {       
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else { 
            $error = curl_errno($ch);
            curl_close($ch);
        }
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


    /*
     *@funtion : to build the reponse infomation
     *
     */
    public function result ( $code=200, $message="ok", $data=null )
    {
        $result['code']= $code;
        $result['message']= $message;
        $result['data']= $data;
        return $result;
    }

	//检查用户是否绑定微信
	public function checkBindWechat($mokaid)
	{
		$check = DB::table('wechats')->where('mokaid','=',$mokaid)->first();
		if($check){
			return true;
		}
		else{
			return false;
		}
	}

}
