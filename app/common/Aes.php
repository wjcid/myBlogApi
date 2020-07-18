<?php
/**
 * +----------------------------------------------------------------------
 * | aes 加密 解密类
 * +----------------------------------------------------------------------
 */
namespace app\common;

class Aes {
    // aes key
    private $key = 'api_blog_keydata';
    // aes iv初始化向量
    private $iv = 'api_blog_ivStr_k';
    /**
     * 解密
     * @param String str 需解密字符串
     * @return String
     */
    public function decrypt($str) {
        $decode = openssl_decrypt($str, 'AES-128-CBC', $this->key, 0, $this->iv);
        $decode = rtrim($decode, "\0");
        return $decode;
    }
    /**
     * 加密
     * @param String str 需加密字符串
     * @return String
     */
    public function encrypt($str) {
        $encode = openssl_encrypt($str, 'AES-128-CBC', $this->key, 0, $this->iv);
        $encode = rtrim($encode, "\0");
        return $encode;
    }
    
}