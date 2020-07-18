<?php
/**
 * +----------------------------------------------------------------------
 * | 空控制器
 * +----------------------------------------------------------------------
 */
namespace app\controller;

class Error extends Base
{
    public function __call($method, $args)
    {
        $this->result([], 10404, '无效的路由地址');
    }
}