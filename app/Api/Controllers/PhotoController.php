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

class ActivityController extends BaseController
{

    public function __construct(){
        parent::__construct();
    }
    //打开大图
    public function detailed(Request $request){

    }
    protected static function small($background, $width, $height, $newfile) {
     list($s_w, $s_h)=getimagesize($background);//获取原图片高度、宽度
     if ($width && ($s_w < $s_h)) {
     $width = ($height / $s_h) * $s_w;
     } else {
     $height = ($width / $s_w) * $s_h;
     }
     $new=imagecreatetruecolor($width, $height);
     $img=imagecreatefromjpeg($background);
     imagecopyresampled($new, $img, 0, 0, 0, 0, $width, $height, $s_w, $s_h);
     imagejpeg($new, $newfile);
     imagedestroy($new);
     imagedestroy($img);
    }

}
