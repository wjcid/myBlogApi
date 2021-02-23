<?php
/**
 * +----------------------------------------------------------------------
 * | 数据统计接口
 * +----------------------------------------------------------------------
 */
namespace app\controller;

use app\model\Visits;
use think\exception\ValidateException;
use think\facade\{Db, Request, Log};

class Statistics extends Base
{
  /**
   * 控制器中间件 [登录、注册 、修改密码不需要鉴权]
   * @var array
   */
  protected $middleware = [
      'app\middleware\Api' => ['except' => []],
  ];

  /**
   * @api {post} /User/login 01、首页统计数据
   * @apiGroup User
   * @apiVersion 1.0.0
   * @apiDescription 系统首页接口


   * @apiParam (响应字段：) {string}         token    Token

   * @apiSuccessExample {json} 成功示例
   * {"code":10200,"msg":"登录成功","time":1563525780,"data":{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhcGkuc2l5dWNtcy5jb20iLCJhdWQiOiJzaXl1Y21zX2FwcCIsImlhdCI6MTU2MzUyNTc4MCwiZXhwIjoxNTYzNTI5MzgwLCJ1aWQiOjEzfQ.prQbqT00DEUbvsA5M14HpNoUqm31aj2JEaWD7ilqXjw"}}
   * @apiErrorExample {json} 失败示例
   * {"code":10400,"msg":"帐号或密码错误","time":1563525638,"data":[]}
   */
  public function homeData() {

  }
}
