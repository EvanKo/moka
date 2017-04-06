<?php

namespace App\Api\Controllers;

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
use Redis;
use Log;

class ActivityController extends BaseController
{

    public function __construct(){
        parent::__construct();
	}

	public function checkMessage(Requests $request)
	{
		$user_message = JWTAuth::toUser();

		$moka_id = $user_message['moka'];
		
		$records = Redis::members('unreadMsg:'.$moka_id);//unreadMsg:$moka_id 未读消息集合
		if($records){ //$records 数据格式 from:来信用户或群聊id
			$data = [];
			for($records as $record){
				$data[] = Redis::hgetall($record);
			}
			return $this->returnMsg('200','ok',$data);
		}
		else{
			return $this->returnMsg('404','Unread message not found');
		}
	}

	public function newChatGroup(Request $request)
	{
		$user_message = JWTAuth::toUser();
		$moka_id = $user_message['moka'];
		while(true){
			$room_id = (time()%1000000).rand(100,1000);
			$isExist = Redis::sismember('AllChatGroups',$room_id);
			if(!$isExist)
				break;
		}
		Redis::sadd('AllChatGroups','group_id:'.$room_id);
		$check = Redis::sadd('group_id:'.$room_id,$moka_id);//添加聊天室成员
		if(!$check)
			Log::warning('Create chatgroup fail, user moka_id:'.$user_message['moka']);
		
	}

    //开启某个聊天或者加入群聊
    public function join(Request $request){

    }

}
