<?php

namespace App\Api\Controllers;

use App\Api\Controllers\BaseController;
use Illuminate\Support\Facades\Session;
use Curl\Curl;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use JWTAuth;

class MainpageController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    //所有数据
    public function main(Request $request){
      $role = JWTAuth::toUser();
      $moka = $request->input('moka',$role['moka']);
      $output = DB::table('Roles')
        ->where('moka',$moka)
        ->get();
      if ($output->count() == 0) {
        $result = $this->returnMsg('500',"error moka");
        return response()->json($result);
      }
      $output = json_decode($output,true);
      $output = $output[0];
      $output['sum'] = DB::table('Records')
        ->where('moka',$moka)
        ->count();
      $result = $this->returnMsg('200',"ok",$output);
      return response()->json($result);
    }
    //模特榜
    public function modelgirls(Request $request){
      $page = $request->input('page',1);
      $area = $request->input('area','null');
      if ($area == 'null') {
        $record = DB::table('Roles')
          ->where('role',0)
          ->orderBy('fans','desc')
          ->skip(($page-1)*15)
          ->limit(15)
          ->select('id','head','name','sex','v','moka')
          ->get();
      }
      else {$record = DB::table('Roles')
        ->where('role',0)
        ->where('area',$area)
        ->orderBy('fans','desc')
        ->skip(($page-1)*15)
        ->limit(15)
        ->select('id','head','name','sex','v','moka')
        ->get();
      }
      if ($record->count() == 0) {
        $result = $this->returnMsg('200','bottom');
        return response()->json($result);
      }
      $result = $this->returnMsg('200','ok',$record);
      return response()->json($result);
    }
    //摄影师版
    public function photographers(Request $request){
      $page = $request->input('page',1);
      $area = $request->input('area','null');
      if ($area == 'null') {
        $record = DB::table('Roles')
          ->where('role',1)
          ->orderBy('fans','desc')
          ->skip(($page-1)*15)
          ->limit(15)
          ->select('id','head','name','sex','v')
          ->get();
      }
      else {$record = DB::table('Roles')
        ->where('role',1)
        ->where('area',$area)
        ->orderBy('fans','desc')
        ->skip(($page-1)*15)
        ->limit(15)
        ->select('id','head','name','sex','v')
        ->get();}
      if ($record->count() == 0) {
        $result = $this->returnMsg('200','bottom');
        return response()->json($result);
      }
      $result = $this->returnMsg('200','ok',$record);
      return response()->json($result);
    }
    //红人榜
    public function hot(Request $request){
      $record['model'] = DB::table('Roles')
        ->where('role',0)
        ->orderBy('value','desc')
        ->limit(6)
        ->select('id','head','name','sex','v','intro','moka')
        ->get();
      $record['photogra'] = DB::table('Roles')
        ->where('role',1)
        ->orderBy('value','desc')
        ->limit(6)
        ->select('id','head','name','sex','v','intro','moka')
        ->get();
      $result = $this->returnMsg('200','ok',$record);
      return response()->json($result);
    }
    //我的订单，通告
    public function activity(Request $request){
      $role = JWTAuth::toUser();
      $page = $request->input('page',1);
      $moka = $request->input('moka',$role['moka']);
      $result = DB::table('Status')
        // ->leftjoin('Activities','Activities.id','=','Status.target_id')
        ->whereRaw('target = 4 and (boss = '.$moka.' or customer = '.$moka.')');
        if ($result->get()->count() == 0) {
          $result = $this->returnMsg('200','ok');
          return response()->json($result);
        }
        $result = $result
        ->pluck('target_id');
      $result = DB::table('Activities')
        ->where('id',$result)
        ->orwhere('moka',$role['moka'])
        ->orderBy('id','desc')
        ->skip(($page-1)*6)
        ->select('id','moka','img','type','view','title','price')
        ->limit(6)
        ->get();
        $number = 0;
        $result = json_decode($result,true);
        foreach ($result as $key) {
          $output[$number]=$key;
          if ($key['moka'] == $role['moka']) {
            $output[$number++]['owner']=1;
          }
          else {
            $output[$number++]['owner']=0;
          }
        }
        $result = $this->returnMsg('200','ok',$output);
        return response()->json($result);
    }
    public function yue(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request,[
        'choose'=>'required|Numeric'
      ]);
      $choose = $request->input('choose');
      $page = $request->input('page',1);

      $moka = $request->input('moka',$role['moka']);
      $result = DB::table('Status')
        ->leftjoin('Orders','Orders.id','=','Status.target_id')
        ->where('Status.target',2)
        ->where('Status.yue',1);
      $output = array();
      if ($choose == 1) {
        $result = $result->where('Status.boss',$moka);
        $result = $result
        ->orderBy('id','desc')
        ->select('Status.id','Status.reserved','Status.customer','Status.boss','Status.target_id','Orders.price','Status.status')
        ->skip(($page-1)*9)
        ->limit(9)
        ->get();
        $number = 0;
        $result = json_decode($result,true);

        foreach ($result as $key) {
          $output[$number]=$key;
          $output[$number]['customer'] = DB::table('Roles')
            ->where('moka',$output[$number]['customer'])
            ->select('id','name','head')
            ->first();
            $output[$number]['boss'] = array();
            $output[$number]['boss']['id'] = $role['id'];
            $output[$number]['boss']['head'] = $role['head'];
          $output[$number++]['boss']['name'] = $role['name'];
        }
      }
      else {
        $result = $result->where('Status.customer',$moka);
        $result = $result
        ->orderBy('id','desc')
        ->select('Status.id','Status.reserved','Status.customer','Status.boss','Status.target_id','Orders.price','Status.status')
        ->skip(($page-1)*9)
        ->limit(9)
        ->get();
        $number = 0;
        $result = json_decode($result,true);

        foreach ($result as $key) {
          $output[$number]=$key;
          $output[$number]['boss'] = DB::table('Roles')
            ->where('moka',$output[$number]['boss'])
            ->select('id','name','head')
            ->first();
            $output[$number]['customer'] = array();
            $output[$number]['customer']['id'] = $role['id'];
            $output[$number]['customer']['head'] = $role['head'];
          $output[$number++]['customer']['name'] = $role['name'];
        }
      }

        $result = $this->returnMsg('200','ok',$output);
        return response()->json($result);
    }
    public function order(Request $request){
      $role = JWTAuth::toUser();
      $this->validate($request,[
        'choose'=>'required|Numeric'
      ]);
      $choose = $request->input('choose');
      $page = $request->input('page',1);

      $moka = $request->input('moka',$role['moka']);
      $result = DB::table('Status')
        ->leftjoin('Orders','Orders.id','=','Status.target_id')
        ->where('Status.target',2)
        ->where('Status.yue','!=',1);
      $output = array();
      if ($choose == 1) {
        $result = $result->where('Status.boss',$moka);
        $result = $result
        ->orderBy('id','desc')
        ->select('Status.id','Status.reserved','Status.customer','Status.boss','Status.target_id','Orders.price','Status.status','Status.name','Status.ps')
        ->skip(($page-1)*9)
        ->limit(9)
        ->get();
        $number = 0;
        $result = json_decode($result,true);

        foreach ($result as $key) {
          $output[$number]=$key;
          $output[$number]['customer'] = DB::table('Roles')
            ->where('moka',$output[$number]['customer'])
            ->select('id','name','head')
            ->first();
            $output[$number]['boss'] = array();
            $output[$number]['boss']['id'] = $role['id'];
            $output[$number]['boss']['head'] = $role['head'];
          $output[$number++]['boss']['name'] = $role['name'];
        }
      }
      else {
        $result = $result->where('Status.customer',$moka);
        $result = $result
        ->orderBy('id','desc')
        ->select('Status.id','Status.reserved','Status.customer','Status.boss','Status.target_id','Orders.price','Status.status','Status.name','Status.ps')
        ->skip(($page-1)*9)
        ->limit(9)
        ->get();
        $number = 0;
        $result = json_decode($result,true);

        foreach ($result as $key) {
          $output[$number]=$key;
          $output[$number]['boss'] = DB::table('Roles')
            ->where('moka',$output[$number]['boss'])
            ->select('id','name','head')
            ->first();
            $output[$number]['customer'] = array();
            $output[$number]['customer']['id'] = $role['id'];
            $output[$number]['customer']['head'] = $role['head'];
          $output[$number++]['customer']['name'] = $role['name'];
        }
      }

        $result = $this->returnMsg('200','ok',$output);
        return response()->json($result);
    }
    public function officelist(Request $request){
      $role = JWTAuth::toUser();
      $moka = $request->input('moka',$role['moka']);
      $page = $request->input('page',1);
      $result = DB::table('Orders')
      ->where('type',2)
      ->where('finish',1)
        ->where('moka',$moka)
        ->orderBy('id','desc')
        ->skip(($page-1)*4)
        ->limit(4)
        ->select('id','title','content','place','photonum','focusphoto','moka'
        ,'price'
        ,'img'
        ,'label')
        ->get();
        $num = 0;
        $output = array( );
      if ($result->count() == 0) {
        $result = $this->returnMsg('200','ok',$output);
        return response()->json($result);
      }

      $result = json_decode($result,true);
        foreach ($result as $key) {
          $output[$num]['data'] = $key;
          $output[$num++]['photo'] = DB::table('Photos')
            ->where('mokaid',$key['id'])
            ->select('id','img_s','ps','imgnum')
            ->get();
        }
      $result = $this->returnMsg('200','ok',$output);
      return response()->json($result);
    }
}
