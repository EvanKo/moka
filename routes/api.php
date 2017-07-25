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
      $api->post('phonecheck', 'LoginController@phonechecks'); //tel,name,sex,password
      //首页榜
      $api->post('model', 'MainpageController@modelgirls'); //finish page,area
      $api->post('photographers', 'MainpageController@photographers'); //finish page,area
      $api->post('hotguys', 'MainpageController@hot'); //
	  $api->post('getCode', 'PayModule\BindController@index');
    $api->post('bind','Patmodule\BindConcontroller@info');
      //微信支付回调
	  $api->any('normalMembernotify', 'PayModule\WechatPayController@normalmembernotify');
	  $api->any('advanceMembernotify', 'PayModule\WechatPayController@advancemembernotify');
	  $api->any('supermeMembernotify', 'PayModule\WechatPayController@supermemembernotify');
	  $api->any('ordernotify', 'PayModule\WechatPayController@ordernotify');
	  $api->any('dateordernotify', 'PayModule\WechatPayController@dateOrderNotify');
      $api->group(['middleware' => 'jwt.api.auth'], function ($api) {
		//充值
		$api->post('recharge','PayModule\PayController@recharge');
		//打赏目标
		$api->post('paytomoka','PayModule\PhotoController@pay');
        //获取金币和钱余额
        $api->get('/user/account','PayModule\PayController@getAccount');
        //金币详情
        $api->get('/user/goldDetail','PayModule\PayController@getGoldDetail');
        //零钱详情
        $api->get('/user/moneyDetail','PayModule\PayController@getMoneyDetail');
		//设置打赏额度
	    $api->post('setfee','PayModule\PayController@setFee');
		//购买会员
		$api->post('buymember', 'PayModule\PayController@member');
      $api->post('update', 'LoginController@update'); //name province city office area intro workexp
		//微信支付
		$api->post('pay', 'PayModule\WechatPayController@unifiedOrder');
       //登录注册相关
        $api->post('role', 'LoginController@roleUpdate');//finish role
        $api->post('head', 'LoginController@headUpdate');//finish head
        $api->post('bg', 'LoginController@bgUpdate');//finish img
        $api->post('body', 'LoginController@body');//finish height,weight,bust,waist,hips,shoe,exp
        $api->post('logout', 'LoginController@logout');//finish
		    $api->post('checkmanager', 'LoginController@checkmanager');//finish tel
		    //聊天
        $api->post('checkMsg', 'ChatModule\ChatController@checkMessage');
        $api->post('sendMsg', 'ChatModule\ChatController@sendMsg');
	  		//websocket用于检验用户token
	  		$api->post('checkUserLogin', 'ChatModule\ChatController@checkUserLogin');
	    $api->post('newGroupChat', 'ChatController@newGroupChat');
	    $api->post('joinGroup', 'ChatController@joinGroup');
		//摄影师发起约拍订单
		$api->post('newDatePhoto','PayModule\DatePhotoController@newDatePhoto');
		//摄影师发起工作室订单
		$api->post('newStudioOrder','PayModule\DatePhotoController@newStudioOrder');
		$api->post('dealDone', 'PayModule\PayController@dealDone');
		//支付约拍
		//$api->post('payDataPhoto','PayModule\PayController@payDataPhoto');
		    //动态
        $api->post('makemoment', 'MomentController@make');//finish img,content
        $api->post('delemoment', 'MomentController@delete');//finish momentid
        $api->post('moment', 'CommonController@moment');//finish id
        //评论
        $api->post('makecomment', 'CommentController@make');//finish target,target_id,answer,answername,content
        $api->post('delecomment', 'CommentController@dele');//finish id
        $api->post('commentlist', 'CommentController@list');//finish target,target_id
        $api->post('mycomment', 'CommentController@my');//finish moka,page
        //赞
        $api->post('zan', 'AppreciateController@handle');//finish kind,key
        $api->post('zanlist', 'AppreciateController@alllist');
        //关注
        $api->post('follow', 'FanController@handle');//finish moka
        $api->post('idols', 'FanController@idol');//finish page
        $api->post('fans', 'FanController@fan');//finish page
        //主页
        $api->post('person', 'MainpageController@main');//finish
           $api->post('myactivity', 'MainpageController@activity');//finish
		        $api->post('myyue', 'MainpageController@yue');//finish
		$api->post('myorder', 'MainpageController@order');//finish
		$api->post('selfrecord', 'CommonController@selfrecord');//finish page
        //订单
        $api->post('makeorder', 'OrderController@make');//finish price,type,img,content
        $api->post('deleorder', 'OrderController@delete');//finish id
        $api->post('order', 'CommonController@order');//finish id
        //制作摩卡
        $api->post('makemoka', 'MokaController@start');//finish size,imgnum
        $api->post('delemoka', 'MokaController@delete');//finish id
        $api->post('savemoka', 'MokaController@save');//finish id
        $api->post('moka', 'CommonController@moka');//finish id
        //ajax上传图片
        $api->post('mokaphoto', 'PhotoController@update');//finish num,img
        $api->post('photodetail', 'PhotoController@detail');//finish id
		$api->post('actphoto', 'PhotoController@actupdate');//finish num,img
		        $api->post('albumphoto', 'PhotoController@albumupdate');//finish num,img
		        $api->post('officephoto', 'PhotoController@officeupdate');//finish num,img
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
        $api->post('startactivity', 'ActivityController@start');//finish key,page
        $api->post('deleactivity', 'ActivityController@delete');//finish key,page
        $api->post('saveactivity', 'ActivityController@save');//finish key,page
        $api->post('areaactivity', 'ActivityController@areaactivity');//finish key,page
        $api->post('activity', 'CommonController@activity');//finish id
		        $api->post('enroll', 'ActivityController@enroll');//finish id

		//认证
        $api->post('auth', 'AuthController@update');//finish name,company,id
        //举报
        $api->post('report', 'ReportController@report');//finish content
        //修改密码
        $api->post('changepassword', 'LoginController@changepassword');//finish content
        //执行，参加
        $api->post('startdeal', 'StatusController@start');//finish content
        $api->post('handledeal', 'StatusController@handle');
		//相册
		$api->post('startalbum', 'AlbumController@new');
		        $api->post('album', 'AlbumController@detail');
		        $api->post('albumdeleteone', 'AlbumController@deleteone');
				        $api->post('albumdeleteall', 'AlbumController@deleteall');
				$api->post('albumlist', 'AlbumController@my');
		//工作室
				$api->post('startoffice', 'OfficeController@start');//finish key,page
				        $api->post('deleoffice', 'OfficeController@delete');//finish key,page
				        $api->post('saveoffice', 'OfficeController@save');//finish key,page
        $api->post('office', 'OfficeController@office');//finish key,page
        $api->post('officelist', 'MainpageController@officelist');//finish id,img1
		$api->post('officecover', 'OfficeController@cover');//finish id,img
	  });
    });
});
