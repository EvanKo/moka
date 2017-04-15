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

    $api->group(['namespace' => 'App\Api\Controllers'], function ($api) {
      $api->post('login', 'LoginController@login');//finish
	  $api->post('register', 'LoginController@register');

	  $api->post('sendMsg', 'ChatController@sendMsg');

      $api->group(['middleware' => 'jwt.api.auth'], function ($api) {
        //登录注册相关
        $api->post('role', 'LoginController@roleUpdate');//finish
        $api->post('head', 'LoginController@headUpdate');//finish
        $api->post('checkmanager', 'LoginController@checkmanager');//finish
        //动态
        $api->post('makemoment', 'MomentController@make');//finish
        $api->post('delemoment', 'MomentController@delete');//finish
        $api->get('moment', 'MomentController@moment');//finish
        //评论
        $api->post('makecomment', 'CommentController@make');//finish
        $api->post('delecomment', 'CommentController@dele');//finish
        $api->post('commentlist', 'CommentController@list');//finish
        //赞
        $api->post('zan', 'AppreciateController@handle');//finish
        $api->post('zanlist', 'AppreciateController@alllist');
        //关注
        $api->post('follow', 'FanController@handle');//finish
        $api->post('idols', 'FanController@idol');//finish
        $api->post('fans', 'FanController@fan');//finish
        //主页
        $api->post('person', 'MainpageController@main');
        //订单
        $api->post('makeorder', 'OrderController@make');//finish
        $api->post('deleorder', 'OrderController@delete');//finish
        // $api->get('moment', 'MomentController@moment');
        //制作摩卡
        $api->post('makemoka', 'MokaController@start');
        // $api->post('deleorder', 'OrderController@delete');//finish
      });
    });
});
