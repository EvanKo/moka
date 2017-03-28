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

class OrderController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    //发订单
    public function make(Request $request){
      $role = JWTAuth::toUser();
      $price = $request->input('price',null);
      $type = $request->input('type',null);
      $content = $request->input('content',null);
      $img = $request->file('img',null);
      $this->returnReq($price,'price');
      $this->returnReq($type,'type');
      $this->returnReq($content,'content');
      $this->returnReq($img,'img');
      $root = public_path().'/photo/order/'.$role['moka'].'/';
      $root2 = '/photo/order/'.$role['moka'].'/';
      if(!file_exists($root)){
        mkdir($root);
      }
      $num = md5(time()).".".$img->getClientOriginalExtension();
      $img->move( $root,$num);
      $input = $request->all();
      $input['img'] = $_SERVER['HTTP_HOST'].$root2.$num;
      $input['imgnum'] = $num;
      $input['moka'] = $role['moka'];
      $result = Order::create($input);
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }
    //删除订单
    public function delete(Request $request){
      $role = JWTAuth::toUser();
      $id = $request->input('id',null);
      $object = Order::find($id);
      if ($object['moka'] != $role['moka']) {
        $result = $this->returnMsg('500','no permission');
        return response()->json($result);
      }
      File::delete(public_path().'/photo/order/'.$object['moka'].'/'.$object['imgnum']);
      AppreciateController::deleall(2,$object['id']);
      CommentController::deleall(2,$object['id']);
      $result = $object->delete();
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }
}
