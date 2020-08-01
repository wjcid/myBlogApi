<?php 
/**
 * +----------------------------------------------------------------------
 * | Redis 互斥锁
 * +----------------------------------------------------------------------
 */
namespace app\common;

use think\facade\Cache;

class RedisLock {

  private $_redis;
  
  public function __construct()
  {
    //获取redis handler资源句柄
    $this->_redis = Cache::handler();

  }
  /**
   * 获取锁
   * @param  String  $key    锁标识
   * @param  Int     $expire 锁过期时间
   * @param  Int     $random   随机值
   * @return Boolean
   */
  public function acquire_lock($key, $random, $expire)
  {
      //设置锁的超时时间，避免释放锁失败，del()操作失败，产生死锁。
      $ret = $this->_redis->set($key, $random, ['nx', 'ex' => $expire]);
      return $ret;
  }
   
  /**
   * 释放锁
   * @param  String  $key 锁标识
   * @param  Int     $random   随机值
   * @return Boolean
   */
  public function release_lock($key, $random)
  {
      //防止操作时间过长，超过了锁的有效时间，导致其他请求拿到了锁,误删其他请求创建的锁
      if ($this->_redis->get($key) == $random) {
        return $this->_redis->del($key);
      }else {
        return 2;
      }
  }
}