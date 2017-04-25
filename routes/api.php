  <?php
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {

    $api->group(['middleware' => 'apiweb','namespace' => 'App\Api\Controllers'], function ($api) {
      $api->post('login', 'LoginController@login');//finish tel password
      $api->post('register', 'LoginController@register'); //tel,name,sex,password
      $api->post('sms', 'LoginController@sessionSet'); //tel,name,sex,password
      $api->post('check', 'LoginController@check'); //tel,name,sex,password

      $api->group(['middleware' => 'jwt.api.auth'], function ($api) {
        //登录注册相关
        $api->post('role', 'LoginController@roleUpdate');//finish role
        $api->post('head', 'LoginController@headUpdate');//finish head
        $api->post('logout', 'LoginController@logout');//finish
		    $api->post('checkmanager', 'LoginController@checkmanager');//finish tel
		    //聊天
        $api->post('sendMsg', 'ChatModule\ChatController@sendMsg');
		    $api->post('checkUserLogin', 'ChatModule\ChatController@checkUserLogin');
		    $api->post('newGroupChat', 'ChatController@newGroupChat');
		    $api->post('joinGroup', 'ChatController@joinGroup');
		    //动态
        $api->post('selfmoka', 'CommonController@selfmoka');//finish page
        $api->post('makemoment', 'MomentController@make');//finish img,content
        $api->post('delemoment', 'MomentController@delete');//finish momentid
        $api->post('moment', 'CommonController@moment');//finish id
        //评论
        $api->post('makecomment', 'CommentController@make');//finish target,target_id,answer,answername,content
        $api->post('delecomment', 'CommentController@dele');//finish id
        $api->post('commentlist', 'CommentController@list');//finish target,target_id
        //赞
        $api->post('zan', 'AppreciateController@handle');//finish kind,key
        $api->post('zanlist', 'AppreciateController@alllist');
        //关注
        $api->post('follow', 'FanController@handle');//finish moka
        $api->post('idols', 'FanController@idol');//finish page
        $api->post('fans', 'FanController@fan');//finish page
        //主页
        $api->post('person', 'MainpageController@main');
        //订单
        $api->post('makeorder', 'OrderController@make');//finish price,type,img,content
        $api->post('deleorder', 'OrderController@delete');//finish id
        $api->post('order', 'CommonController@order');//finish id
        //制作摩卡
        $api->post('selfmoka', 'CommonController@selfmoka');//finish page
        $api->post('makemoka', 'MokaController@start');//finish size,imgnum
        $api->post('delemoka', 'MokaController@delete');//finish id
        $api->post('savemoka', 'MokaController@save');//finish id
        $api->post('moka', 'CommonController@moka');//finish id
        //ajax上传图片
        $api->post('mokaphoto', 'PhotoController@update');//finish num,img
        $api->post('photodetail', 'PhotoController@detail');//finish id
        //附近
        $api->post('near', 'CommonController@near');//finish page,area
        //好友
        $api->post('friend', 'CommonController@friend');//finish page
        //热门
        $api->post('hot', 'CommonController@hot');//finish page
        //搜索
        $api->post('search', 'LoginController@search');//finish key,page
        $api->post('area', 'LoginController@area');//finish page
        //活动
        $api->post('makeactivity', 'ActivityController@make');//finish key,page
        $api->post('deleactivity', 'ActivityController@delete');//finish key,page
        $api->post('activity', 'CommonController@activity');//finish id
        //认证
        $api->post('authphoto', 'AuthController@photo');//finish img,choose
        $api->post('auth', 'AuthController@update');//finish name,company,id

      });
    });
});
