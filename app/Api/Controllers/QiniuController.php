<?php
namespace App\Api\Controllers;

 // require_once 'qiniu/autoload.php';
 use Qiniu\Auth;
 use Qiniu\Storage\UploadManager;
 use Qiniu\Storage\BucketManager;
 use Qiniu\Processing\PersistentFop;

class QiniuController
{

 static function update($file,$end){

 $accessKey = 'RUTxOoX5K9jJiQtef7kk4w5_uRHBFKeCw6IfETaZ';
 $secretKey = '7z_zrCHM2dOlJ7W1upnUem6NjYrbQvhJcNmE0PJN';
 $auth = new Auth($accessKey, $secretKey);
 $bucket = 'bbtrainchapter';

 $token = $auth->uploadToken($bucket);
   // 要上传文件的本地路径
   $filePath = $file;
   // 上传到七牛后保存的文件名
   $key = $end;
   // 初始化 UploadManager 对象并进行文件的上传
   $uploadMgr = new UploadManager();
   // 调用 UploadManager 的 putFile 方法进行文件的上传
   list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
   if ($err !== null) {
       return $err;
   } else {
       return $ret;
   }
 }

static function delete($end){

   $accessKey = 'RUTxOoX5K9jJiQtef7kk4w5_uRHBFKeCw6IfETaZ';
   $secretKey = '7z_zrCHM2dOlJ7W1upnUem6NjYrbQvhJcNmE0PJN';
   $auth = new Auth($accessKey, $secretKey);
   $bucket = 'bbtrainchapter';
   $key = $end;
  //初始化BucketManager
  $bucketMgr = new BucketManager($auth);
  //你要测试的空间， 并且这个key在你空间中存在
  $err = $bucketMgr->delete($bucket, $key);
  if ($err !== null) {
      return $err;
  } else {
      return "Success!";
    }
}
static function small($key,$end){
$accessKey = 'RUTxOoX5K9jJiQtef7kk4w5_uRHBFKeCw6IfETaZ';
  $secretKey = '7z_zrCHM2dOlJ7W1upnUem6NjYrbQvhJcNmE0PJN';
  $auth = new Auth($accessKey, $secretKey);
  $bucket = 'bbtrainchapter';
  // 上传到七牛后保存的文件名
  $pfop = new PersistentFop($auth,$bucket);
  $saveas = base64_encode("bbtrainchapter:".$end);
  $fops = 'imageView2/0/w/200/h/200|saveas/'.$saveas;


  list($id, $err) = $pfop->execute($key, $fops);
  if ($err !== null) {
      return $err;
  } else {
      return  "PersistentFop Id: ".$id;
  }
}
}
