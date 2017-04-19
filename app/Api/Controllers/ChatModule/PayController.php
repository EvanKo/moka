<?php
namespace App\Api\GoModule\Controllers;

use Log;
use App\Api\Controllers\BaseController;
use App\Http\Requests\checkDriverLocation;
use App\Wechat;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use App\OrderRecord;


class PayController extends BaseController
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

    public function notify(Request $request){
		Log::info('Notify function message');
		$msg = array();
		$postStr = file_get_contents('php://input');
		$msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		Log::info('msg:'.$msg['result_code']);
		if($msg['result_code']=='SUCCESS'){
		  $openid = $msg['openid']; 
		  $user = Wechat::where('openid','=',$openid)->first();
			//Log记录数据
			Log::info('user openid:'.$openid);
			Log::info('transaction_id:'.$msg['transaction_id']);//微信支付订单号
			Log::info('total_fee:'.$msg['total_fee']);//金额
			Log::info('time_end:'.$msg['time_end']);	//时间	
			Log::info('userPhone:'.$user->tel);
	 	  DB::table('order_records')->where(['userphone'=>$user->tel,'isAccept'=>'3'])
			->update(['isAccept'=>'4']);
			Redis::del('usecar:'.$user->tel);
		}
		else{
		Log::info('FAIL:'.$msg['openid'].'fail to pay');
		} 
	}	
	
    public function unifiedOrder()
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";    
        $inputObj=array();

        $inputObj['appid']="wx95eaefe010f7c7e8";//微信支付分配的公众账号ID
        $inputObj['mch_id']="1410843402";//微信支付分配的商户号
        $inputObj['nonce_str']=$this->getNonceStr();//随机字符串，长度要求在32位以内
        $inputObj['body']="滴达订单";//商品简单描述，该字段请按照规范传递
        $inputObj['out_trade_no']="1410843402".date("YmdHis").rand(111,999);//商户系统内部订单号，要求32个字符内、且在同一个商户号下唯一
        
        $inputObj['spbill_create_ip']=$_SERVER['REMOTE_ADDR'];//APP和网页支付提交用户端ip
        $inputObj['notify_url']="https://api.didame.com:8088/api/notify";//异步接收微信支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数
        $inputObj['trade_type']="JSAPI";//取值如下：JSAPI，NATIVE，APP等。公众号支付未JSAPI
        $token = JWTAuth::getToken();
        $user_json = JWTAuth::toUser($token);
        $user = json_decode($user_json, true);
        
        $openid=$user['openid'];
        $phone=$user['tel'];//用户手机号
        $order=OrderRecord::where(['userphone'=>$phone,'isAccept'=>3])->first();
	Log::info('user order num'.count($order));
        if (count($order)>0) {
            $price=$order->price;
	    Log::info($phone.'->price:'.$order->price);
        }
        $inputObj['openid']=$openid;//$user['openid'];//公众号支付，此参数必传，此参数为微信用户在商户对应appid下的唯一标识
        //从数据库去出价格，返回
	$price = round($price,2); //四舍五入
	$amount = $price*100;
	//$amount=100; //付款多少( /分）
        $inputObj['total_fee']=$amount;//订单总金额，单位为分

        
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
        $string = $string. "&key="."didavtmer168didavtmer168didavtme";
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);

        $inputObj['sign']=$result;//通过签名算法计算得出的签名值

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
                $string = $string. "&key="."didavtmer168didavtmer168didavtme";
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
}
