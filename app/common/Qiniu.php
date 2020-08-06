<?php
/**
 * +----------------------------------------------------------------------
 * | 七牛云上传.删除文件
 * +----------------------------------------------------------------------
 */
namespace app\common;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\facade\Env;

class Qiniu
{
    private $accessKey;
    private $secretKey;
    private $bucket;
    private $domain;
    //鉴权对象
    private $auth;
    //构建鉴权对象
    public function __construct() {
        $this->accessKey = Env::get('qiniu.qiniu_access_key');
        $this->secretKey = Env::get('qiniu.qiniu_secret_key');
        $this->bucket = Env::get('qiniu.qiniu_bucket');
        $this->domain = Env::get('qiniu.qiniu_domain');
        $this->auth = new Auth($this->accessKey, $this->secretKey);
    }
    public function upload($file,$filename)
    {
        $filename=str_replace('\\','/',$filename);
        // 生成上传 Token
        $token = $this->auth->uploadToken($this->bucket);
        $uploadMgr = new UploadManager();
        // 文件上传。
        $res = $uploadMgr->putFile($token, $filename, $file);
        if($res[1]==null){ //成功
            return $this->domain.$filename;
        }else{
            return '';
        }
    }
    //$filename 为数据库中存储的url,七牛云删除只需要文件名
    public function delFile($filename)
    {
        // 管理资源
        $bucketManager = new \Qiniu\Storage\BucketManager($this->auth);
        $filename = strrchr($filename,'/');// “/”最后出现的位置并返回从该位置到字符串结尾的所有字符
        $filename = substr($filename,1);
        // 删除文件操作
        $res = $bucketManager->delete($this->bucket, $filename);
        if (is_null($res)) {
            // 为null成功
            return true;
        }else {
            return false;
        }
    }
}