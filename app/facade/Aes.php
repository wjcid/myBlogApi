<?php
namespace app\facade;

use think\Facade;

class Aes extends Facade
{
    protected static function getFacadeClass()
    {
    	return 'app\common\Aes';
    }
}