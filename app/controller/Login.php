<?php
/**
 * +----------------------------------------------------------------------
 * | 登录验证接口
 * +----------------------------------------------------------------------
 */
namespace app\controller;

use app\service\JwtAuth;
use app\model\Users;
use app\facade\Aes;
use think\exception\ValidateException;
use think\facade\{Db, Request, Log};

class Login extends Base
{
    /**
     * 控制器中间件 [登录、注册 、修改密码不需要鉴权]
     * @var array
     */
    protected $middleware = [
        'app\middleware\Api' => ['except' => ['login', 'register', 'editPwd']],
    ];

    /**
     * @api {post} /User/login 01、用户登录
     * @apiGroup User
     * @apiVersion 1.0.0
     * @apiDescription 系统登录接口，返回 token 用于接口身份验证

     * @apiParam (请求参数：) {string}     		account 登录账号
     * @apiParam (请求参数：) {string}     		password 登录密码 md5 9a3912b0263fadb4380771c2f30bc2c3   aes z/6MdUtHbSmXniqzZXT7YA==

     * @apiParam (响应字段：) {string}     		token    Token

     * @apiSuccessExample {json} 成功示例
     * {"code":10200,"msg":"登录成功","time":1563525780,"data":{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhcGkuc2l5dWNtcy5jb20iLCJhdWQiOiJzaXl1Y21zX2FwcCIsImlhdCI6MTU2MzUyNTc4MCwiZXhwIjoxNTYzNTI5MzgwLCJ1aWQiOjEzfQ.prQbqT00DEUbvsA5M14HpNoUqm31aj2JEaWD7ilqXjw"}}
     * @apiErrorExample {json} 失败示例
     * {"code":10400,"msg":"帐号或密码错误","time":1563525638,"data":[]}
     */
    public function login()
    {
        $username = Request::param('account');
        $password = Request::param('password');
        $paramInfo = Request::param();
        try {
            validate(\app\validate\User::class)
                ->scene('edit')
                ->check($paramInfo);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $this->result(['apiParam' => 'error'], 10300, $e->getError());
        }
        //解密
        $decode = Aes::decrypt($password);
        // 校验用户名密码
        $where1['account'] = $username;
        $where1['password'] = md5($decode);
        $where2['phone'] = $username;
        $where2['password'] = md5($decode);
        $user = Users::where(function ($query) use ($where1) {
                $query->where($where1);
            })->whereOr(function ($query) use ($where2) {
                $query->where($where2);
            })->find();
        if (empty($user)) {
            $this->result([], 10400, '帐号或密码错误');
        } else {
            //获取jwt的句柄
            $jwtAuth = JwtAuth::getInstance();
            $token = $jwtAuth->setUid($user['id'])->encode()->getToken();
            //$nav = $this->userPermList($user['permission']);
            //更新信息
            Users::where('id', $user['id'])
                ->update(['login_ip' => ip2long(Request::ip())]);
            //写入日志
            $data = ['login_time' => time(), 'ip' => Request::ip(), 'account' => $username];
            Log::write($data, 'notice');
            $this->result(['token' => $token], 10200, '登录成功');
        }
    }
    /**
     * @api {post} /User/register 02、用户注册
     * @apiGroup User
     * @apiVersion 1.0.0
     * @apiDescription  系统注册接口，返回是否成功的提示，需再次登录

     * @apiParam (请求参数：) {string}     		username 用户名
     * @apiParam (请求参数：) {string}     		password 密码
     * @apiParam (请求参数：) {string}     		unit 单位

     * @apiSuccessExample {json} 成功示例
     * {"code":1,"msg":"注册成功","time":1563526721,"data":[]}
     * @apiErrorExample {json} 失败示例
     * {"code":0,"msg":"已被注册","time":1563526693,"data":[]}
     */
    public function register(){
        $username = Request::param('name');
        $password = Request::param('password');
        $unit = Request::param('unit');
        $phone = Request::param('phone');
        $email = Request::param('email');
        $verify_code = Request::param('verify_code');
        // 验证码校验
        if ($verify_code != '1234') {
            $this->result([], 0, '验证码错误');
        }
        // 密码长度不能低于6位
        if (strlen($password) < 6) {
            $this->result([], 0, '密码长度不能低于6位');
        }

        // 防止重复
        $id = Db::name('user_info')->where('phone', '=', $phone)->find();
        if ($id) {
            $this->result([], 0, '手机已被注册');
        }
        //解密
        $key = "sea_sand_keydata";
        $iv = "sea_sand_ivStr_k";
        // 解密前处理
        $decode = openssl_decrypt($password, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
        $decode = rtrim($decode, "\0");
        // 注册入库
        $data = [];
        $data['name']           = $username;
        $data['phone']          = $phone;
        $data['unit']           = $unit;
        $data['email']           = $email;
        $data['remark']           = '';
        $data['status']           = '0';
        $data['permission']     = '1,0,0,0,0,0,0,0,0,0';
        $data['password']        = md5($decode);
        $data['f_time'] = $data['l_time'] = $data['t_time'] = time();
        $data['create_ip']       = $data['last_login_ip'] = Request::ip();
        $id = Db::name('user_info')->insertGetId($data);
        if ($id) {
            $this->result([], 1, '注册成功');
        } else {
            $this->result([], 0, '注册失败');
        }
    }


    /**
     * @api {post} /User/editPwd 04、修改密码
     * @apiGroup User
     * @apiVersion 1.0.0
     * @apiDescription  修改密码，返回成功或失败提示

     * @apiParam (请求参数：) {string}     		token Token
     * @apiParam (请求参数：) {string}     		oldPassword 原密码
     * @apiParam (请求参数：) {string}     		newPassword 新密码

     * @apiSuccessExample {json} 成功示例
     * {"code":1,"msg":"密码修改成功","time":1563527107,"data":[]}
     * @apiErrorExample {json} 失败示例
     * {"code":0,"msg":"token已过期","time":1563527082,"data":[]}
     */
    public function editPwd(){
        $phone = Request::param('phone');
        $verify_code = Request::param('verify_code');
        $newPassword = Request::param('password');
        $id = Db::name('user_info')->where('phone', '=', $phone)->find();
        if (empty($id)) {
            $this->result([], 0, '手机号不存在');
        }
        // 验证码校验
        if ($verify_code != '1234') {
            $this->result([], 0, '验证码错误');
        }
        // 密码长度不能低于6位
        if (strlen($newPassword) < 6) {
            $this->result([], 0, '密码长度不能低于6位');
        }
        //解密
        $key = "sea_sand_keydata";
        $iv = "sea_sand_ivStr_k";
        // 解密前处理
        $decode = openssl_decrypt($newPassword, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
        $decode = rtrim($decode, "\0");
        //更新信息
        $user = Users::find($this->getUid());
        $user->password = md5($decode);
        $user->save();
        $this->result([], 1, '密码修改成功');
    }

    /**
     * @api {post} /User/editInfo 05、修改信息
     * @apiGroup User
     * @apiVersion 1.0.0
     * @apiDescription  修改用户信息，返回成功或失败提示

     * @apiParam (请求参数：) {string}     		token Token
     * @apiParam (请求参数：) {string}     		sex 性别 [1男/0女]
     * @apiParam (请求参数：) {string}     		qq  qq
     * @apiParam (请求参数：) {string}     		mobile  手机号

     * @apiSuccessExample {json} 成功示例
     * {"code":0,"msg":"修改成功","time":1563507660,"data":[]}
     * @apiErrorExample {json} 失败示例
     * {"code":0,"msg":"token已过期","time":1563527082,"data":[]}
     */
    public function editInfo(){
        $data['sex']    = trim(Request::param("sex"));
        $data['qq']     = trim(Request::param("qq"));
        $data['mobile'] = trim(Request::param("mobile"));
        if ($data['mobile']) {
            // 不可和其他用户的一致
            $id = Users::
                where('mobile', $data['mobile'])
                ->where('id', '<>', $this->getUid())
                ->find();
            if ($id) {
                $this->result([], 0, '手机号已存在');
            }
        }
        // 更新信息
        Users::where('id', $this->getUid())
            ->update($data);
        $this->result([], 0, '修改成功');
    }

}