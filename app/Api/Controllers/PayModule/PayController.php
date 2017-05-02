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

class ActivityController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    //充值
    public function recharge(Request $request){

    }
    //打赏一个目标，支付一份订单
    public function pay(Request $request){

    }
    //购买会员
	public function member(Request $request)
	{
		$user_data = JWTAuth::toUser();
		$input['moka'] = $user_data('moka');
		$input['type'] = $request->input('type');
		$input['amount'] = $request->input('amount');
		$input['tel'] = $user_data['tel'];
		$input['status'] = 0;
		$input['name'] = $user_data['name'];

		$result = DB::table('PayRecords')->insert($input);
		if($result){
			return $this->returnMsg('200','ok');
		}else{ 
			return $this->returnMsg('500','fail');
		}
    }
}
