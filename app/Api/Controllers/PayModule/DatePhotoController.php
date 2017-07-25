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
use App\Status;
use App\Record;
use JWTAuth;
use DB;
use File;

class DatePhotoController extends BaseController
{

    public function __construct(){
        parent::__construct();
	}

	/**
	 * @author guyi
	 *@desc 摄影师发起约拍订单
	 *@param mote 模特id
	 *@param price 价格
	 **/
	public function newDatePhoto(Request $request)
	{
		$token = JWTAuth::getToken();
		$user_data = JWTAuth::toUser($token);
		
		if(!$request->input('price')){
			return $this->returnMsg('500','价格不能为空');
		}
		if(!$request->input('boss')){
			return $this->returnMsg('501','订单接受者不能为空');
		}
		//$check_bind = $this->checkBindWechat($user_data['moka']);
		//if($check_bind){
			$input['moka'] = $user_data['moka'];
			$input['title'] = '约拍';//标题
			$input['content'] = '约拍';//约拍内容
			$input['price'] = $request->input('price');//价格
			$input['type'] = 1;//约定约拍订单类型为1
			$input['area'] = $user_data['area'];//地区
			$input['boss'] = $request->input('boss');//被约拍的人id
			$input['reserved'] = $request->input('reserved');		
			//$input['finish'] = 3;//默认为3

			//$input['lasting'] = '';
			$input['reserved'] = $request->input('reserved');//约拍日期
			$input['img'] = 'null';

			$response = Order::create($input);
			$response = json_decode($response,true);
			$input['ps'] = $request->input('ps');//备注
			$result1 = $this->addStatus($response['id'],$input);
			$result2 = $this->addRecord($response['id'],$input);
			if($result1 && $result2){
				return $this->returnMsg('200','ok',$result1['id']);
			}else{
				return $this->returnMsg('505','create fail');
			}
		//}else{
		//	return $this->returnMsg('504','not bind wechat');
		//}
	}
	
	//工作室订单
	public function newStudioOrder(Request $request)
	{
		$user_data = JWTAuth::toUser();
		$input['customer'] = $user_data['moka'];
		$input['target'] = 2;
		$input['target_id'] = $request->input('orderNum');		
		$input['reserved'] = $request->input('reserved');		
		if(!$input['target_id']){
			$result = $this->returnMsg('500',"orderNum");
			          return response()->json($result);
		}			
		$order = DB::table('Orders')
			->where('id',$input['target_id'])
			->where('type',2)->get();
		if($order->get()->count()==0){
			$result = $this->returnMsg('500',"office not existed");
			          return response()->json($result);
		}
		$boss = $order->pluck('moka');
		$price = $order->pluck('price');
		$name = $order->pluck('title');
		 $input['boss'] = $boss[0];		
		$input['price'] = $price[0];
		$input['name'] = $name[0];		
		
		//return $input;
		$input['yue'] = 0;
		$input['status'] = 1;
		$input['ps'] = $request->input('ps');
		$result = Status::create($input);
		if($result){
			return $this->returnMsg('200','ok');
		}else {
			return $this->returnMsg('500','fail');
		}
	}	 
	//表Status增加记录
	public function addStatus($id,$data)
	{
		$insert_data['customer'] = $data['moka'];//用户mokaid
		$insert_data['target'] = 2;
		$insert_data['yue'] = 1;
		$insert_data['target_id'] = $id;//表Order的订单号
		$insert_data['status'] = 1;
		$insert_data['ps'] = $data['ps'];

		$insert_data['boss'] = $data['boss'];
		$result = Status::create($insert_data);
		if ($result)
			return json_decode($result,true);
		else return FALSE;
	}	
	//表Records添加记录，被约拍者
	public function addRecord($id,$data)
	{
		$insert_data['moka'] = $data['boss'];//被约拍者mokaid
		$insert_data['target'] = 2;
		$insert_data['target_id'] = $id;//表Order的订单号
		$insert_data['status'] = 1;
		$insert_data['area'] = $data['area'];
		$insert_data['view'] = 0;
		$result = Record::create($insert_data);
		if ($result)
			return TRUE;
		else return FALSE;
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
