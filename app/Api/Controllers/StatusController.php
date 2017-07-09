<?php

namespace App\Api\Controllers;

use App\Api\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Status;
use JWTAuth;
use DB;

class StatusController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }

    //开启新业务
    public function start(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request,[
        'target'=>'required|Numeric',
        'target_id'=>'required|Numeric',
      ]);
      $target = $request->input('target');
      $target_id = $request->input('target_id');
      $count = DB::table('Status')
      ->where('target',$target)
        ->where('target_id',$target_id)
        ->where('customer',$role['moka'])
        ->get();
      if ($count->count() != 0) {
        $result = $this->returnMsg('500',"cant join");
        return response()->json($result);
      }

      $create = DB::table('Records')
        ->where('target',$target)
        ->where('target_id',$target_id);
        if ($create->get()->count() == 0) {
          $result = $this->returnMsg('500','error target');
          return response()->json($result);
        }
        $create = $create
        ->pluck('moka');
      $input['boss'] = $create[0];
      $input['customer'] = $role['moka'];
      $input['target_id'] = $target_id;
      $input['target'] = $target;
      $input['status'] = 1;
      $result = Status::create($input);
      if ($input['boss']!=$input['customer']) {
        $message = [
              'type'=>'activity',
              'from'=>$role['moka'],
              'to'=>$input['boss'],
              'token'=>strval(JWTAuth::getToken()),
              'time'=>date('Y-m-s H:i:s')];
        // 建立连接，@see http://php.net/manual/zh/function.stream-socket-client.php
        $client = stream_socket_client('tcp://127.0.0.1:7273', $errno, $errmsg, 1);
        if(!$client)exit("can not connect");
        // // 模拟超级用户，以文本协议发送数据，注意Text文本协议末尾有换行符（发送的数据中最好有能识别超级用户的字段），这样在Event.php中的onMessage方法中便能收到这个数据，然后做相应的处理即可
         fwrite($client,json_encode($message)."\n");
      }
      $result = $this->returnMsg('200',"ok",$role['head']);
      return response()->json($result);
    }

    //管理业务
    public function handle(Request $request){
      $role=JWTAuth::toUser();
      $this->validate($request,[
        'id'=>'required|Numeric',
        'status'=>'required|Numeric',
      ]);
      $id = $request->input('id');
      $status = $request->input('status');
      $deal = DB::table('Status')
        ->where('id',$id)
        ->where('status',1)
        ->where('boss',$role['moka']);
      if ($deal->get()->count() == 0) {
        $result = $this->returnMsg('500',"error id");
        return response()->json($result);
      }
      $deal->update(['status'=>$status]);
      $result = $this->returnMsg('200',"ok");
      return response()->json($result);
    }
}
