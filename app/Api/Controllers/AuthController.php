<?php
namespace App\Api\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\TestCase;
use JWTAuth;
use App\Auth;
use DB;
use File;


class AuthController extends BaseController
{
    /**
     * The authentication guard that should be used.
     *
     * @var string
     */
    public function __construct()
    {
        parent::__construct();

    }

    //资料
     public function update(Request $request){
       $role = JWTAuth::toUser();
       $moka = $role['moka'];
       $auth = DB::table('Auths')->where('moka',$moka)
       ->get();
       if ($auth->count() == 0) {
         $result = $this->returnMsg('500',"photo first");
         return response()->json($result);
       }
       else {
         $name = $request->input('name',null);
         if ( $this->returnReq($name,'name') != '200') {
           return $this->returnReq($name,'name');
         }
         $company = $request->input('company',null);
         if ( $this->returnReq($company,'company') != '200') {
           return $this->returnReq($company,'company');
         }
         $id = $request->input('id',null);
         if ( $this->returnReq($id,'id') != '200') {
           return $this->returnReq($id,'id');
         }
         $result = DB::table('auths')->where('moka',$moka)
          ->update(['authentication_name'=>$name,
            'authentication'=>$company,
            'identification'=>$id]);
          $result = $this->returnMsg('200',"ok",$result);
          return response()->json($result);
       }

     }
    //认证图片上传
     public function photo(Request $request){
      $role = JWTAuth::toUser();
      $choose = $request->input('choose',null);
      if ( $this->returnReq($choose,'choose') != '200') {
        return $this->returnReq($choose,'choose');
      }
      $img = $request->file('img',null);
      if ( $this->returnReq($img,'img') != '200') {
        return $this->returnReq($img,'img');
      }
      $moka = $role['moka'];
      $auth = DB::table('Auths')->where('moka',$moka)
      ->get();
      $root = public_path().'/photo/auth/'.$moka.'/';
      $root2 = '/photo/auth/'.$moka.'/';
      if(!file_exists($root)){
        mkdir($root);
      }
      $num = md5(time()).".".$img->getClientOriginalExtension();
      $img->move( $root,$num);
      $imgaddr = $_SERVER['HTTP_HOST'].$root2.$num;
      if ($auth->count() == 0) {
        if ($choose == '1') {
          $input['identification_img'] = $imgaddr;//手持身份证
        }
        else if ($choose == '2') {
          $input['bussiness_img'] = $imgaddr;//营业执照
        }
        $input['moka'] = $moka;
        Auth::create($input);
        $result = $this->returnMsg('200',"ok",$imgaddr);
        return response()->json($result);
      }
      else {
        if ($choose == '1') {
          $object = Auth::where('moka',$moka)->first();
          $num = $object['identification_img'];
          if ($num != '') {
            $num = trim($num,$_SERVER['HTTP_HOST']);
            File::delete(public_path().$num);
          }
          DB::table('Auths')->where('moka',$moka)
            ->update(['identification_img'=>$imgaddr]);
          $result = $this->returnMsg('200',"ok",$imgaddr);
          return response()->json($result);
        }
        else if ($choose == '2') {
          $object = Auth::where('moka',$moka)->first();
          $num = $object['bussiness_img'];
          if ($num != '') {
            $num = trim($num,$_SERVER['HTTP_HOST']);
            File::delete(public_path().$num);
          }
          $input['bussiness_img'] = $imgaddr;//营业执照
        }
      }
     }



    public function logout(){
      // $token = $request->get('token');
        JWTAuth::refresh();
        $result = $this->returnMsg('200','ok');
        return response()->json(compact('result'));
    }

}
