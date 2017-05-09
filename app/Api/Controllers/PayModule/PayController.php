<?php

namespace App\Api\Controllers\Paymodule;

use App\Api\Controllers\BaseController;
use App\Api\Controllers\AppreciateController;
use App\Api\Controllers\CommentController;
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
