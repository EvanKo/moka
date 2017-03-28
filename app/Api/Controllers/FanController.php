<?php

namespace App\Api\Controllers;

use App\Api\Controllers\BaseController;
use Illuminate\Support\Facades\Session;
use Curl\Curl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use App\Http\Requests;
use App\Fan;
use App\Role;
use JWTAuth;
use DB;

class FanController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    //关注或者取消关注
    public function handle(Request $request){
      $role = JWTAuth::toUser();
      $moka = $request->input('moka',null);
      if ($moka == null ) {
        $result = $this->returnMsg('500','request error');
        return response()->json($result);
      }
      $query = 'idol = '.$moka.' and fan = '.$role['moka'];
      $object = Fan::whereRaw($query);
      if (!$object->get()->isEmpty()) {
        $result = $object->delete();
        DB::table('Roles')
          ->where('moka', $role['moka'])
          ->update(['idols' => 'idols'-1]);
        DB::table('Roles')
          ->where('moka', $moka)
          ->update(['fans' => 'fans'-1]);
        $result = $this->returnMsg('200','disfollowed',$result);
        return response()->json($result);
      }
      else {
        $object = Role::where('moka',$moka)->first();
        $input['fan'] = $role['moka'];
        $input['fanhead'] = $role['head'];
        $input['fanname'] = $role['name'];
        $input['fansex'] = $role['sex'];
        $input['idol'] = $moka;
        $input['idolhead'] = $object['head'];
        $input['idolname'] = $object['name'];
        $input['idolsex'] = $object['sex'];
        $result = Fan::create($input);
        DB::table('Roles')
          ->where('moka', $role['moka'])
          ->update(['idols' => 'idols'+1]);
        DB::table('Roles')
          ->where('moka', $moka)
          ->update(['fans' => 'fans'+1]);
        $result = $this->returnMsg('200','followed',$result);
        return response()->json($result);
      }
    }
    //关注列表
    public function idol(Request $request){
      $role = JWTAuth::toUser();
      $page = $request->input('page',1);
      $moka = $role['moka'];
      $data = DB::table('Fans')->where('fan',$moka)
      ->orderBy('id','desc')
      ->select('id','idol','idolname','idolhead','idolsex');
      $data = $data->skip(($page-1)*15)
      ->limit(15)
      ->get();
      $result['data'] = $data;
      if ($result == null) {
        $result = $this->returnMsg('200','The end');
        return response()->json($result);
      }
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }
    //粉丝列表
    public function fan(Request $request){
      $role = JWTAuth::toUser();
      $page = $request->input('page',1);
      $moka = $role['moka'];
      $data = DB::table('Fans')->where('idol',$moka)
      ->orderBy('id','desc')
      ->select('id','fan','fanname','fanhead','fansex');
      $data = $data->skip(($page-1)*15)
      ->limit(15)
      ->get();
      $result['data'] = $data;
      if ($result == null) {
        $result = $this->returnMsg('200','The end');
        return response()->json($result);
      }
      $result = $this->returnMsg('200','ok',$result);
      return response()->json($result);
    }

}
