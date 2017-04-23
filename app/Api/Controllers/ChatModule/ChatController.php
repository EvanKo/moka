<?php

namespace App\Api\Controllers\ChatModule;

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

class ChatController extends BaseController
{

    public function __construct(){
        parent::__construct();
	}

	public function checkMessage(Requests $request)
	{
		$token = JWTAuth::getToken()
		$user_message = JWTAuth::toUser($token);

		$moka_id = $user_message['moka'];

		$records = DB::table('ChatRecords')->where('to','=',$moka_id)->get();//unreadMsg:$moka_id 未读消息集合
		if($records->count()){
			return $this->returnMsg('200','ok',['MsgNum'=>$records->count(),'records'=>$records]);
		}
		else{
			return $this->returnMsg('404','Unread message not found');
		}
	}

	public function newChatGroup(Request $request)
	{
		$token = JWTAuth::getToken()
		$user_message = JWTAuth::toUser($token);
		$moka_id = $user_message['moka'];
		while(true){
			$group_id = (time()%1000000).rand(100,1000);
			$isExist = Redis::sismember('AllChatGroups',$group_id);
			if(!$isExist)
				break;
		}
		Redis::sadd('AllChatGroups','group_id:'.$group_id);
		$check = Redis::sadd('group_id:'.$group_id,$moka_id);//添加聊天室成员
		if(!$check)
			Log::warning('Create chatgroup fail, user moka_id:'.$user_message['moka']);
	}

	public function checkUserLogin(Request $request)
	{
		$token = JWTAuth::getToken();
		$user_json = JWTAuth::toUser($token);
		return $user_json;
	}
    //加入群聊
	public function joinGroup(Request $request)
	{
		$group_id = $request->input('group_id');
		$user_message = JWTAuth::toUser();
		$moka_id = $user_message['moka'];

		$check = Redis::sismember('AllChatGroups',$group_id);
		if($check){
			Redis::sadd('group_id:'.$group_id,$moka_id);
			return $this->returnMsg('200','ok');
		}
		else return $this->returnMsg('404','Can not find the group');
	}
	//开启聊天
	public function sendMsg(Request $request)
	{
		$type = $request->input('type');
		$from = $request->input('from');
		$fromName = $request->input('fromName');
		$to = $request->input('to');
		$toName = $request->input('toName');
		$content = $request->input('content');

		$message = ['type'=>$type,
					'from'=>$from,
					'fromName'=>$fromName,
					'to'=>$to,
					'toName'=>$toName,
					'content'=>$content,
					'time'=>date('Y-m-s H:i:s')];
		// 建立连接，@see http://php.net/manual/zh/function.stream-socket-client.php
		$client = stream_socket_client('tcp://127.0.0.1:7273', $errno, $errmsg, 1);
		if(!$client)exit("can not connect");
		// // 模拟超级用户，以文本协议发送数据，注意Text文本协议末尾有换行符（发送的数据中最好有能识别超级用户的字段），这样在Event.php中的onMessage方法中便能收到这个数据，然后做相应的处理即可
		 fwrite($client,json_encode($message)."\n"); 
		return $this->returnMsg('200','ok');
	}
}
