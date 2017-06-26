<?php
    require_once 'app/Qiniu/autoload.php';
    // 引入鉴权类
    use Qiniu\Auth;
     use Qiniu\Storage\UploadManager;
     use Qiniu\Storage\BucketManager;
     use Qiniu\Processing\PersistentFop;
    // 需要填写你的 Access Key 和 Secret Key
    $accessKey = 'RUTxOoX5K9jJiQtef7kk4w5_uRHBFKeCw6IfETaZ';
   $secretKey = '7z_zrCHM2dOlJ7W1upnUem6NjYrbQvhJcNmE0PJN';
   $auth = new Auth($accessKey, $secretKey);
   $bucket = 'bbtrainchapter';

 $token = $auth->uploadToken($bucket);
   // 要上传文件的本地路径
   $filePath = 'public/photo/head/timg.jpeg';
   // 上传到七牛后保存的文件名
   $key = 'head/timg.jpeg';
   // 初始化 UploadManager 对象并进行文件的上传
   $uploadMgr = new UploadManager();
   // 调用 UploadManager 的 putFile 方法进行文件的上传
   list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
   if ($err !== null) {
       return $err;
   } else {
       return $ret;
   }
 