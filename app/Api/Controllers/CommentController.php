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
    public static function two($target,$targetid){
      $query = 'select * from comments where target = \''.$target.'\' and target_id =\''.$target_id.'\' limit 0,2';
      $result = DB::select($query);
      return $result;
    }
    //评论列表，加载所有评论，每次请求10条
    public function list(Request $request){
      $target = $request->input('kind',null);
      $target_id = $request->input('key',null);
      $page = $request->input('page',1);
      if ($target == null || $target_id == null) {
        $result = $this->returnMsg('500','request error');
        return response()->json($result);
      }
      $page = ($page-1)*10;
      $query = 'select * from comments where target = \''.$target.'\' and target_id =\''.$target_id.'\' limit '.$page.',10';
      $result = DB::select($query);
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
