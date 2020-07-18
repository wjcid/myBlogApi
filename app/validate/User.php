<?php
/**
 * +----------------------------------------------------------------------
 * | 用户相关API参数验证
 * +----------------------------------------------------------------------
 */
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule =   [
        'account'  => 'require|max:25',
        'password'   => 'require|min:6',   
    ];
    
    protected $message  =   [
        'account.require' => '账号不能为空',
        'account.max'     => '账号最多不能超过25个字符',
        'password.require'   => '密码不能为空',
        'password.min'  => '密码最少6位',   
    ];
    
    protected $scene = [
        'edit'  =>  ['account','password'],
    ];    
}