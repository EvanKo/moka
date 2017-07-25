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
use App\GoldRecord;
use JWTAuth;
use DB;

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
	//设置打赏额度
	public function setFee (Request $request)
	{
		$fee = $request->input("fee");
		if(!$fee){
			return $this->returnMsg('504','请输入正确的参数');
		}

		$user_data = JWTAuth::toUser();
		$user_data = json_decode($user_data,true);

		$result = DB::table('Roles')->where('moka','=',$user_data['moka'])
			->update(['fee'=>$fee]);
		if($result){
			return $this->returnMsg('200','ok');
		}else {
			return $this->returnMsg('500','fail');
		}
	}
		
    //打赏一个目标，支付一份订单
	public function pay(Request $request){
		$user_data = JWTAuth::toUser();
		$check = $this->checkBindWechat($user_data['moka']);
		if($check){
			$input['amount'] = $request->input('amount');
			$input['tomoka'] = $request->input('tomoka');

			$input['moka'] = $user_datar['moka'];
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

	//订单完成，增加boss的余额
	public function dealDone(Request $request)
	{
		$to_mokaid = $request->input('boss');
		$id = $request->input('id');
		if(!$id || !$to_mokaid){
			return $this->returnMsg('500','id,boss is required');
		}
		$check1 = DB::table('Status')->where('id','=',$id)->first();
		$check2  = DB::table('Orders')->where('id', '=', $check1->target_id)->first();
		if(!$check1 || !$check2){
			return $this->returnMsg('404', 'can not find the order');
		}
		$price = $check2->price;
		DB::table('Status')->where('id','=',$id)->update(['status'=>5]);
		$amount = DB::table('Roles')->where('moka', '=', $to_mokaid)->select('money')->first();	
		$result = DB::table('Roles')->where('moka', '=', $to_mokaid)->update(['money'=>$price+$amount->money]);
		if($result){
			return $this->returnMsg('200','ok, finishd the order');
		}else{
			return $this->returnMsg('500','fail');
		}
	}
	//提现
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
	
	/**
	 * 获取金币和零钱
	 *
	 * @param Request $request
	 * @return void
	 */
	public function getAccount(Request $request)
	{
		$userInfo = JWTAuth::toUser();
		if(!$userInfo){
			return $this->returnMsg('500','token invalid');
		}
		$data['money'] = $userInfo['money'];
		$data['gold'] = $userInfo['gold_account'];
		return $this->returnMsg('200','ok',$data);
	}
	
	/**
	 * 获取金币详细
	 *
	 * @param Request $request
	 * @return void
	 */
	public function getGoldDetail(Request $request)
	{
		$userInfo = JWTAuth::toUser();
		if(!$userInfo){
			return $this->returnMsg('500','token invalid');
		}
		$rest = $userInfo['gold_account'];		
		$mokaid = $userInfo['moka'];
		$data = GoldRecord::where(['mokaid'=>$mokaid,'type'=>'1'])->get();
		return $this->returnMsg('200','ok',[ 'remain_money'=>$rest,'detail'=>$data ]);
	}

	/**
	 * 获取零钱详细
	 *
	 * @param Request $request
	 * @return void
	 */
	public function getMoneyDetail(Request $request)
	{
		$userInfo = JWTAuth::toUser();
		$rest = $userInfo['money'];
		if(!$userInfo){
			return $this->returnMsg('500','token invalid');
		}
		$mokaid = $userInfo['moka'];
		$data = GoldRecord::where(['mokaid'=>$mokaid,'type'=>'2'])->get();
		return $this->returnMsg('200','ok',[ 'remain_money'=>$rest,'detail'=>$data ]);
	}

	//检查用户余额是否足够
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

	//获取用户微信openid
	public function getOpenId($mokaid)
	{
		$wechatdata = DB::table('wechats')->where('mokaid','=',$mokaid)->first();
		if($wechatdata){
			return $wechatdata->openid;
		}else{
			return false;
		}
	}
	//检查微信绑定
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
