<?php

namespace App\Api\Controllers\Paymodule;

use App\Api\Controllers\BaseController;
use App\Api\Controllers\AppreciateController;
use App\Api\Controllers\CommentController;
use App\Api\Controllers\PayModule\EnterprisePayController;
use Illuminate\Support\Facades\Session;
use Curl\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Order;
use JWTAuth;
use DB;
use File;

class PayController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    //充值
    public function recharge(Request $request){
		$user_data = JWTAuth::toUser();
		$check = $this->checkBindWechat($user_data['moka']);
		if($check){
			$input['type'] = 4;
			$input['amount'] = $request->input('amount');

			$input['moka'] = $user_data('moka');
			//给自己充值
			$input['tomoka'] = $user_data('moka');
			$input['tel'] = $user_data['tel'];
			$input['status'] = 0;
			$input['name'] = $user_data['name'];
	
			$result = DB::table('PayRecords')->insert($input);
			if($result){
				return $this->returnMsg('200','ok');
			}else{ 
				return $this->returnMsg('504','fail');
			}
		}else{
			return $this->returnMsg('500','wechat not bind');
		}
    }
    //打赏一个目标，支付一份订单
	public function pay(Request $request){
		$user_data = JWTAuth::toUser();
		$check = $this->checkBindWechat($user_data['moka']);
		if($check){
			$input['amount'] = $request->input('amount');
			$input['tomoka'] = $request->input('tomoka');

			$input['moka'] = $user_data('moka');
			$input['tel'] = $user_data['tel'];
			$input['name'] = $user_data['name'];
			$input['status'] = 0;
			$input['type'] = '4';

			$result = DB::table('PayRecords')->insert($input);
			if($result){
				return $this->returnMsg('200','ok');
			}else{ 
				return $this->returnMsg('500','fail');
			}
		}else{
			return $this->returnMsg('500','wechat not bind');
		}

    }
    //购买会员
	public function member(Request $request)
	{
		$user_data = JWTAuth::toUser();
		$check = $this->checkBindWechat($user_data['moka']);
		if($check){
			$input['type'] = $request->input('type');
			$input['amount'] = $request->input('amount');
			$input['time'] = $request->input('time');		

			$input['moka'] = $user_data('moka');
			$input['tel'] = $user_data['tel'];
			$input['status'] = 0;
			$input['name'] = $user_data['name'];

			$result = DB::table('PayRecords')->insert($input);
			if($result){
				return $this->returnMsg('200','ok');
			}else{ 
				return $this->returnMsg('500','fail');
			}
		}else{
			return $this->returnMsg('500','wechat not bind');
		}
	}

	public function getCash(Request $request)
	{
		$money = $request->input('money');
		$userInfo = JWTAuth::toUser();
		$mokaid = $userInfo['moka'];
		$openid = $this->getOpenId($mokaid);
		if(!$openid)
			return $this->returnMsg('500','not bind wechat');
		$check = $this->checkMoney($mokaid,$money);
		if($check){
			$pay = new EnterprisePayController();
			$pay->amount = $money*100;
			$pay->openid = $openid;
			$result = $pay->send();
			return $result;
		}else{
			return $this->returnMsg('403','not enough money');
		}
	}

	public function checkMoney($mokaid,$money)
	{
		$data = DB::table('Roles')->where('moka','=',$mokaid)->first();
		$account = $data->money;
		if($account<$money){
			return False;
			Log::warning('Unvalid request from enterprice-pay, id:'.$mokaid);
		}else{
			return True;
		}
	}

	public function getOpenId($mokaid)
	{
		$wechatdata = DB::table('wechats')->where('mokaid','=',$mokaid)->first();
		if($wechatdata->count()){
			return $wechatdata->openid;
		}else{
			return false;
		}
	}

	public function checkBindWechat($mokaid)
	{
		$check = DB::tables('wechat')->where('mokaid','=',$mokaid)->first();
		if($check->count()){
			return true;
		}
		else{
			return false;
		}
}
