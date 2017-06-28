<?php
namespace App\Api\Controllers;

 require_once public_path().'/Qiniu/autoload.php';
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
   if ($err != null) {
       return 500;
   } else {
       return 200;
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
  if ($err != null) {
      return 500;
  } else {
      return 200;
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
  if ($err != null) {
      return 500;
  } else {
      return 200;
  }
}
static function deleteall($limit){

   $accessKey = 'RUTxOoX5K9jJiQtef7kk4w5_uRHBFKeCw6IfETaZ';
   $secretKey = '7z_zrCHM2dOlJ7W1upnUem6NjYrbQvhJcNmE0PJN';
   $auth = new Auth($accessKey, $secretKey);
   $bucket = 'bbtrainchapter';
  //初始化BucketManager
  $bucketMgr = new BucketManager($auth);
  //你要测试的空间， 并且这个key在你空间中存在
    $prefix = $limit;

    list($iterms, $marker, $err) = $bucketMgr->listFiles($bucket, $prefix);
   //  var_dump($iterms[0]['key']);
   if ($err == null) {
    foreach ($iterms as $key) {
      $err = $bucketMgr->delete($bucket, $key['key']);
      if ($err != null) {
          return 500;
      } else {
          return 200;
      }
    }
  }
    else {
       return 500;
   }
}
static function deleteone($key){

   $accessKey = 'RUTxOoX5K9jJiQtef7kk4w5_uRHBFKeCw6IfETaZ';
   $secretKey = '7z_zrCHM2dOlJ7W1upnUem6NjYrbQvhJcNmE0PJN';
   $auth = new Auth($accessKey, $secretKey);
   $bucket = 'bbtrainchapter';

  //初始化BucketManager
    $bucketMgr = new BucketManager($auth);
    $err = $bucketMgr->delete($bucket, $key);
    if ($err != null) {
        return 500;
    } else {
        return 200;
    }
  }
}
