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
       $auth = DB::table('Auths')->where('moka',$moka);
       $this->validate($request, [
         'realname' => 'required',
         'company' => 'required',
         'companyname' => 'required',
         'img' => 'required|Image',
         'idcardnumber' => 'required',
       ]);
       $img = $request->File('img');
       if ($auth->get()->count() == 0) {
         $root = public_path().'/photo/auth/';
         if(!file_exists($root)){
            mkdir($root);
          }
          $num = $moka.".".$img->getClientOriginalExtension();
          $img->move($root,$num);
          $imgaddr = $_SERVER['HTTP_HOST'].'/photo/auth/'.$num;
          $input = $request->all();
          $input['img'] = $imgaddr;
          $input['moka'] = $role['moka'];
          $result = Auth::create($input);
         $result = $this->returnMsg('200',"ok",$result);
         return response()->json($result);
       }
       else {
         $root = public_path().'/photo/auth/';
         if(!file_exists($root)){
            mkdir($root);
          }
          $num = $moka.".".$img->getClientOriginalExtension();
          $img->move($root,$num);
          $imgaddr = $_SERVER['HTTP_HOST'].'/photo/auth/'.$num;
          $input = $request->all();
          $input['img'] = $imgaddr;
         $result = $auth
            ->update($input);
          $result = $this->returnMsg('200',"changed",$result);
          return response()->json($result);
       }
     }
    public function logout(){
      // $token = $request->get('token');
        JWTAuth::refresh();
        $result = $this->returnMsg('200','ok');
        return response()->json(compact('result'));
    }

}
