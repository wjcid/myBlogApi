<?php
/**
 * +----------------------------------------------------------------------
 * | 文章管理API参数验证
 * +----------------------------------------------------------------------
 */
namespace app\validate;

use think\Validate;

class Article extends Validate
{
    protected $rule =   [
        'id' => 'require|number',
        'type'  => 'require|in:1,2,3', 
        'title' => 'require', 
        'pic_url' => 'require',
        'content' => 'require', 
        'tag' => 'require',
    ];
    
    protected $message  =   [
        'id.require' => '文章ID不能为空',
        'id.number' => '文章ID错误',
        'type.require' => '类型不能为空',
        'type.in'     => '类型只支持选择1|2|3',
        'title.require' => '标题不能为空',
        'pic_url.require' => '文章插图路径不能为空',
        'content.require' => '文章内容不能为空',
        'tag.require' => '文章标签不能为空',
    ];
    
    protected $scene = [
        'list'  =>  ['type'],
        'add'  =>  ['type','title','pic_url','content','tag'],
        'edit'  =>  ['id','type','title','pic_url','content','tag'],
        'del' => ['id'],
        'addRead' => ['id','type'],
    ];    
}