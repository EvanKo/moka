<?php

namespace App\Api\Controllers;

use App\Api\Controllers\BaseController;
use Illuminate\Support\Facades\Session;
use DB;
use Curl\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Comment;
use JWTAuth;

class CommentController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    //回复评论
    public function make(Request $request){
      $role = JWTAuth::toUser();
      $moka = $role['moka'];
      $author = $role['name'];
      $target = $request->input('target',null);
      $target_id = $request->input('target_id',null);
      $answer = $request->input('answer',null);
      $answername = $request->input('answername',null);
      $content = $request->input('content'," ");
      if ($target == null||$target_id == null) {
        $result = $this->returnMsg('500','request error');
        return response()->json($result);
      }
      $input['target'] = $target;
      $input['target_id'] = $target_id;
      $input['moka'] = $moka;
      $input['author'] = $author;
      $input['content'] = $content;
      $input['answer'] = $answer;
      $input['head'] = $role['head'];
      $input['answername'] = $answername;
      $create = DB::table('Records')
        ->where('target',$target)
        ->where('target_id',$target_id);
        if ($create->get()->count() == 0) {
          $result = $this->returnMsg('500','error target');
          return response()->json($result);
        }
        $create = $create
        ->pluck('moka');
      $input['to'] = $create[0];
      // return strval(JWTAuth::getToken());
      if ($create[0] == $input['author']) {
        		$message = [
                  'type'=>'comment',
        					'from'=>$moka,
                  'to'=>$input['to'],
        					'token'=>strval(JWTAuth::getToken()),
        					'time'=>date('Y-m-s H:i:s')];
        		// 建立连接，@see http://php.net/manual/zh/function.stream-socket-client.php
        		$client = stream_socket_client('tcp://127.0.0.1:7273', $errno, $errmsg, 1);
        		if(!$client)exit("can not connect");
        		// // 模拟超级用户，以文本协议发送数据，注意Text文本协议末尾有换行符（发送的数据中最好有能识别超级用户的字段），这样在Event.php中的onMessage方法中便能收到这个数据，然后做相应的处理即可
        		 fwrite($client,json_encode($message)."\n");
            //  return 'ok';
      }
      else {
        $message = [
              'type'=>'comment',
              'from'=>$moka,
              'to'=>$input['to'],
              'token'=>strval(JWTAuth::getToken()),
              'time'=>date('Y-m-s H:i:s')];
        // 建立连接，@see http://php.net/manual/zh/function.stream-socket-client.php
        $client = stream_socket_client('tcp://127.0.0.1:7273', $errno, $errmsg, 1);
        if(!$client)exit("can not connect");
        // // 模拟超级用户，以文本协议发送数据，注意Text文本协议末尾有换行符（发送的数据中最好有能识别超级用户的字段），这样在Event.php中的onMessage方法中便能收到这个数据，然后做相应的处理即可
         fwrite($client,json_encode($message)."\n");
         $message2 = [
               'type'=>'comment',
               'from'=>$moka,
               'to'=>$input['answer'],
               'token'=>strval(JWTAuth::getToken()),
               'time'=>date('Y-m-s H:i:s')];
         // 建立连接，@see http://php.net/manual/zh/function.stream-socket-client.php
         // // 模拟超级用户，以文本协议发送数据，注意Text文本协议末尾有换行符（发送的数据中最好有能识别超级用户的字段），这样在Event.php中的onMessage方法中便能收到这个数据，然后做相应的处理即可
          fwrite($client,json_encode($message2)."\n");
      }
      $result = Comment::create($input);
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }
    //删除评论
    public function dele(Request $request){
      $role = JWTAuth::toUser();
      $moka = $role['moka'];
      $id = $request->input('id',null);
      if ($id == null) {
        $result = $this->returnMsg('500','request error');
        return response()->json($result);
      }
      if ($object = Comment::find($id)) {
        $result = $object->delete();
        $result = $this->returnMsg('200','ok',$result);
        return response()->json($result);
      }
      $result = $this->returnMsg('500','request error');
      return response()->json($result);
    }
    //动态列表只显示前两条
    public static function two($target,$target_id){
      $data = DB::table('Comments')
        ->where('target',$target)
        ->where('target_id',$target_id);
      $result['sum'] = $data->get()->count();
      $result['comment'] = $data->limit(2)->get();
      return $result;
    }
    //评论列表，加载所有评论，每次请求10条
    public function list(Request $request){
      $target = $request->input('target',null);
      $target_id = $request->input('target_id',null);
      $page = $request->input('page',1);
      if ($target == null || $target_id == null) {
        $result = $this->returnMsg('500','request error');
        return response()->json($result);
      }
      $page = ($page-1)*10;
      $result = DB::table('Comments')
        ->where('target',$target)
        ->where('target_id',$target_id)
        ->skip($page)
        ->limit(10)
        ->get();
      if ($result == null) {
        $result = $this->returnMsg('200','The end');
        return response()->json($result);
      }
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }
    //评论列表，加载所有评论，每次请求10条
    public function my(Request $request){
      $role = JWTAuth::touser();
      $moka = $request->get('moka',$role['moka']);
      $page = $request->input('page',1);
      $page = ($page-1)*10;
      $result = DB::table('Comments')
        ->where('to',$moka)
        ->orwhere('answer',$moka)
        ->skip($page)
        ->limit(10)
        ->get();
      if ($result == null) {
        $result = $this->returnMsg('200','The end');
        return response()->json($result);
      }
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }
    //删除其他项目是连着删掉评论
    public static function deleall($target,$target_id){
      $query = 'target = '.$target.' and target_id = '.$target_id;
      $object = Comment::whereRaw($query);
      $result = $object->delete();
      return $result;
    }
}
