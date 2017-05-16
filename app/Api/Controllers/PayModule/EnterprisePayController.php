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

class EnterprisePayController extends BaseController
{
    
    private $mch_appid = "wxa99e4ef76debee57";//微信公众号appid,微信分配的公众账号ID（企业号corpid即为此appId）
    private $mchid = "1462118902";//商户ID，微信支付分配的商户号
    private $nonce_str = "";//随机字符串，不长于32位
    private $sign = ""; //签名在send时生成
    private $partner_trade_no = "";//商户订单号
    private $openid ="";//接收方的openID  
    private $check_name="NO_CHECK";//校验用户姓名选项。NO_CHECK：不校验真实姓名。FORCE_CHECK：强校验真实姓名（未实名认证的用户会校验失败，无法转账）。OPTION_CHECK：针对已实名认证的用户才校验真实姓名（未实名认证用户不校验，可以转账成功）
    private $re_user_name="";//收款用户姓名,可选。收款用户真实姓名。如果check_name设置为FORCE_CHECK或OPTION_CHECK，则必填用户真实姓名
    private $amount = "";//企业付款金额，单位 分，最小一元
    private $desc="摩卡提现";//企业付款操作说明信息。必填
    private $spbill_create_ip="121.40.220.52";//调用接口的机器Ip地址,即脚本文件所在的IP

    private $key="mokaappmoakappmokaappmokaapp";//商户支付密钥
          
    //证书
    private $apiclient_cert='/var/www/cert/apiclient_cert.pem'; 
    private $apiclient_key='/var/www/cert/apiclient_key.pem';
    private $rootca='/var/www/cert/rootca.pem';
    
    
    private $ePay_inited=false;
  
    private $error = "ok"; //init
    

    /**
     * EnterprisePay::__construct()
     * @return void
     */
    function __construct(){
        parent::__construct();
    }
    
    public function err(){
        return $this->error;
    } 
    public function error(){
        return $this->err();
    }
    /**
     * EnterprisePay::newEPay()
     * 构造新付款
     * @param mixed $toOpenId
     * @param mixed $amount 金额分
     * @return void
     */
    public function newEPay($toOpenId,$amount){
    Log::info("企业支付:toOpenId:$toOpenId\namount:$amount"); 
	if(!is_numeric($amount)){
            $this->error = "金额参数错误";
            return;
        }elseif($amount<100){
            $this->error = "金额太小";
            return;
        }elseif($amount>200000){
            $this->error = "金额太大";
            return;
        }
       
        $this->gen_nonce_str();//构造随机字串

        $this->gen_partner_trade_no();//构造订单号

        $this->setOpenId($toOpenId);
        $this->setAmount($amount);
        $this->ePay_inited = true; //标记企业付款已经初始化完毕可以发送
         
        //每次new 都要将分享的内容给清空掉，否则会出现残余被引用
        $this->share_content= "";
        $this->share_imgurl = "";
        $this->share_url = "";
    }

    /**
     * EnterprisePay::send()
     * 发起企业付款
     * 构造签名
     * @return boolean $success
     */
    public function send($url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers"){
        $this->newEPay($this->openid,101);
        if(!$this->ePay_inited){
            $this->error .= "(付款初始化失败)";
            return "付款初始化失败"; //未初始化完成
        }
          
        $this->gen_Sign(); //生成签名
        
        //构造提交的数据        
        $xml = $this->genXMLParam();
        //$result = $this->result('201',var_dump($xml));
        //return response()->json($result);
        $rsxml = simplexml_load_string($xml);    
        //提交xml,curl
        //$url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
        $ch = curl_init();      
        curl_setopt($ch,CURLOPT_TIMEOUT,10);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);     
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT,$this->apiclient_cert);     
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY,$this->apiclient_key);   
      
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
        $data = curl_exec($ch);

        if($data){
            curl_close($ch);    
            $rsxml = simplexml_load_string($data);
		Log::info('result_code'.(string)$rsxml->result_code);
            //$result = $this->result('201',var_dump((string)$rsxml->return_code));
            //return response()->json($result);
            if((string)$rsxml->return_code == 'SUCCESS' and ((string)$rsxml->result_code)=='SUCCESS'){
				$mokaid = this->getMokaId($this->openid);

				DB::beginTransaction();
				$data = DB::table('Roles')->where('mokaid',$mokaid)->first();
				$account = $data->money;
				$rest = ($account*100-$this->amount)/100;
				$data->update(['money'=> $rest]);
				DB::commit();
                $result = $this->result('200', 'OK');
                return response()->json($result);
            }else{
                $result = $this->result('203',(string)$rsxml->return_msg);
                return response()->json($result);  
            }
            
        }else{ 
            $errorNo=curl_errno($ch);
            curl_close($ch);
            $result = $this->result('204',$errorNo);
            return response()->json($result); 
        }

    }    
	public function getMokaId($openid)
	{
		$user = DB::table('wechats')->where('openid',$openid)->first();
		return $user->mokaid;
	}

       
    public function setOpenId($openid){
        $this->openid = $openid;
    }
    
    /**
     * EnterprisePay::setAmount()
     * 设置付款金额
     * @param  $price 单位 分
     * @return void
     */
    public function setAmount($price){
        $this->amount = $price;
    }
    
    
    private function gen_nonce_str(){
        $this->nonce_str = strtoupper(md5(mt_rand().time())); //确保不重复而已
    }
    
    private function gen_Sign(){
        unset($param); 
        $param["mch_appid"]=$this->mch_appid;//
        $param["mchid"]=$this->mchid;//
        $param["nonce_str"]=$this->nonce_str;//
        //$param['sign']=$this->sign;//
        $param["partner_trade_no"] = $this->partner_trade_no;//
        $param["openid"]=$this->openid;//
        $param['check_name']=$this->check_name;//
        $param["amount"]=$this->amount;//
        $param['desc']=$this->desc;//
        $param['spbill_create_ip']=$this->spbill_create_ip;//
      
        ksort($param); //按照键名升序排序
        
        //$sign_raw = http_build_query($param)."&key=".$this->apikey;
        $sign_raw = "";
        foreach($param as $k => $v){
            $sign_raw .= $k."=".$v."&";
        }
        $sign_raw .= "key=".$this->key;
        //$result = $this->result('201',var_dump($sign_raw));
        //return response()->json($result);
        file_put_contents("sign.raw",$sign_raw);//debug
        $this->sign = strtoupper(md5($sign_raw));
    }
    
    /**
     * EnterprisePay::genXMLParam()
     * 生成post的参数xml数据包
     * 注意生成之前各项值要生成，尤其是Sign
     * @return $xml
     */
    public function genXMLParam(){
        $xml = "<xml>
            <mch_appid>".$this->mch_appid."</mch_appid>
            <mchid>".$this->mchid."</mchid>
            <nonce_str>".$this->nonce_str."</nonce_str>
            <partner_trade_no>".$this->partner_trade_no."</partner_trade_no>
            <openid>".$this->openid."</openid>
            <check_name>".$this->check_name."</check_name>
            <amount>".$this->amount."</amount>
            <desc>".$this->desc."</desc>
            <spbill_create_ip>".$this->spbill_create_ip."</spbill_create_ip>
            <sign>".$this->sign."</sign>
            </xml>";
        
        return $xml;
    }
    
    /**
     * EnterprisePay::gen_partner_trade_no()
     *  商户订单号（每个订单号必须唯一） 
     *  组成：mchid+yyyymmdd+10位一天内不能重复的数字。 
    * 接口根据商户订单号支持重入， 如出现超时可再调用。 
     * @return void
     */
    private function gen_partner_trade_no(){
        //生成一个长度10，的阿拉伯数字随机字符串
        $rnd_num = array('0','1','2','3','4','5','6','7','8','9');
        $rndstr = "";
        while(strlen($rndstr)<10){
            $rndstr .= $rnd_num[array_rand($rnd_num)];    
        }
         
        $this->partner_trade_no = $this->mchid.date("Ymd").$rndstr;
        //die($this->partner_trade_no);
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
