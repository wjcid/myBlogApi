<?php
namespace app\facade;

use think\Facade;

class Qiniu extends Facade
{
    protected static function getFacadeClass()
    {
    	return 'app\common\Qiniu';
    }
}