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
}
