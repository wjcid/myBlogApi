<?php
/**
 * +----------------------------------------------------------------------
 * | 七牛云上传
 * +----------------------------------------------------------------------
 */
namespace app\common;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\facade\Env;

class Qiniu
{
    public function upload($file,$filename)
    {
        $filename=str_replace('\\','/',$filename);
        $accessKey=Env::get('qiniu.qiniu_access_key');
        $secretKey=Env::get('qiniu.qiniu_secret_key');
        $bucket=Env::get('qiniu.qiniu_bucket');
        $domain=Env::get('qiniu.qiniu_domain');
        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);
        // 生成上传 Token
        $token = $auth->uploadToken($bucket);
        $uploadMgr = new UploadManager();
        // 文件上传。
        $res = $uploadMgr->putFile($token, $filename, $file);
        if($res[1]==null){ //成功
            return $domain.$filename;
        }else{
            return '';
        }
    }
}