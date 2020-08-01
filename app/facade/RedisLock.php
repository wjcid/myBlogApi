<?php
namespace app\facade;

use think\Facade;

class RedisLock extends Facade
{
    protected static function getFacadeClass()
    {
      return 'app\common\RedisLock';
    }
}