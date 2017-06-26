<?php

namespace App\Api\Controllers;

use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Illuminate\Contracts\Validation\Validator;
use App\Role;
use DB;

class BaseController extends Controller
{
    use Helpers;

    /****
     * BaseController constructor.
     */
    public function __construct()
    {

    }
    //返回信息
    public function returnMsg($code='200', $message='ok', $data=''){
        $arr['code'] = $code;
        $arr['message'] = $message;
        $arr['data'] = $data;
        return $arr;
    }
    //validate
    public function returnReq($query,$name){
        if ($query == null) {
          $arr['code'] = '500';
          $arr['message'] = $name.' required';
          return response()->json($arr);
        }
        return '200';
    }
    //计算身价
    public function value($id){
      $role = Role::find($id);
      $login = $role['login'];
      $fans = $role['fans'];
      $fee = $role['fee'];
      $value = $login + $fans + 2 * $fee;
      $result = DB::table('Roles')
        ->where('id',$id)
        ->update(['value' => $value]);
      return true;
    }

}
