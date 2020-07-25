<?php
/**
 * +----------------------------------------------------------------------
 * | 前端页面展示接口
 * +----------------------------------------------------------------------
 */
namespace app\controller;

use think\exception\ValidateException;
use think\facade\{Request, Cache};

class ArtWeb extends Base
{
    /**
     * 控制器中间件 [ 不需要鉴权 ]
     * @var array
     */
    protected $middleware = [
        'app\middleware\Api' => ['except' => ['addRead','artList','rankList','artContent']],
    ];

    //请求参数
    protected $paramInfo;

    // 文章列表Key前缀
    protected $artKey;

    // 排行榜Key
    protected $rankKey;

    /**
     * 构造方法
     * @access public
     */
    public function __construct() {
        $this->paramInfo = Request::param();
        $this->valiParam('list');
        switch ($this->paramInfo['type']) {
            case 1:
                $this->artKey = 'developArt:';
                $this->rankkey = 'derank';
                break;
            case 2:
                $this->artKey = 'readArt:';
                $this->rankkey = 'rerank';
                break;
            case 3:
                $this->artKey = 'liveArt:';
                $this->rankkey = 'lirank';
                break;
        }
    }
    /**
     * 参数验证
     * @access protected
     * @param $scene {string} 验证类型
     */
    protected function valiParam($scene) {
        try {
            validate(\app\validate\Article::class)
                ->scene($scene)
                ->check($this->paramInfo);
                return;
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $this->result(['apiParam' => 'error'], 10300, $e->getError());
        }
    }
    /**
     * @api {post} /ArtWeb/addRead 01、添加文章阅读量
     * @apiGroup ArtWeb
     * @apiVersion 1.0.0
     * @apiDescription 文章阅读接口

     * @apiParam (请求参数：) {number}     		id 文章ID

     * @apiSuccessExample {json} 成功示例
     * {"code":10200,"msg":"获取数据成功","time":1594969422,"data":{"total":0,"list":[]}}
     * @apiErrorExample {json} 失败示例
     * {"code":10300,"msg":"类型只支持选择1|2|3","time":1594969705,"data":{"apiParam":"error"}}
     */
    public function addRead() {
        $this->valiParam('addRead');
        $member = $this->paramInfo['id'];
        //判断该ID 是否存在（防止无效ID刷榜）

        //将有序集合成员$zkey 的值加1
        Cache::zincrby($this->rankkey,1,$member);
        $this->result([], 10200, 'success');
    }

    /**
     * @api {post} /ArtWeb/artList 02、文章列表
     * @apiGroup ArtWeb
     * @apiVersion 1.0.0
     * @apiDescription 前端文章列表接口

     * @apiParam (请求参数：) {string}     		type 文章类型

     * @apiSuccessExample {json} 成功示例
     * {"code":10200,"msg":"获取数据成功","time":1594969422,"data":{"total":0,"list":[]}}
     * @apiErrorExample {json} 失败示例
     * {"code":10300,"msg":"类型只支持选择1|2|3","time":1594969705,"data":{"apiParam":"error"}}
     */
    public function artList() {    
        // 获取所有ID
        $artids = Cache::smembers('artid:'.$this->paramInfo['type']);
        //获取文章列表
        foreach ($artids as $value) {
            $arr[] = Cache::hgetall($this->artKey.$value);
        }
        $this->result(['artList'=>$arr], 10200, 'success');
        
    }

    /**
     * @api {post} /ArtWeb/tagArtList 03、文章列表（按标签分类）
     * @apiGroup ArtWeb
     * @apiVersion 1.0.0
     * @apiDescription 前端文章列表接口

     * @apiParam (请求参数：) {string}           type 文章类型
     * @apiParam (请求参数：) {string}           tagname 标签名

     * @apiSuccessExample {json} 成功示例
     * {"code":10200,"msg":"获取数据成功","time":1594969422,"data":{"total":0,"list":[]}}
     * @apiErrorExample {json} 失败示例
     * {"code":10300,"msg":"类型只支持选择1|2|3","time":1594969705,"data":{"apiParam":"error"}}
     */
    public function tagArtList() {    
        // 获取所有ID
        $artids = Cache::smembers('artid:'.$this->paramInfo['type']);
        //获取改类型所有文章列表
        $info = array();
        foreach ($artids as $value) {
            $info[] = Cache::hgetall($this->artKey.$value);
        }
        $arr = array();
        foreach ($info as $key => $value) {
            if ($value['tag'] == $this->paramInfo['tagname']) {
                $arr[] = $value;
            }
        }
        $this->result(['artList'=>$arr], 10200, 'success');
        
    }

    /**
     * @api {post} /ArtWeb/rankList 04、文章排行榜
     * @apiGroup ArtWeb
     * @apiVersion 1.0.0
     * @apiDescription 文章排行榜接口

     * @apiParam (请求参数：) {string}     		type 文章类型

     * @apiSuccessExample {json} 成功示例
     * {"code":10200,"msg":"获取数据成功","time":1594969422,"data":{"total":0,"list":[]}}
     * @apiErrorExample {json} 失败示例
     * {"code":10300,"msg":"类型只支持选择1|2|3","time":1594969705,"data":{"apiParam":"error"}}
     */
    public function rankList() {
        $rank = Cache::zrevrange($this->rankkey,0,9,true);
        foreach ($rank as $key => $value) {

            $title = Cache::hmget($this->artKey.$key,['title']);
            $rankList[] = array(
                'id' => $key,
                'title' => $title['title'],
                'read_num' => $value
            );
        }
        $this->result(['rankList'=>$rankList], 10200, 'success');
    }

    /**
     * @api {post} /ArtWeb/artContent 05、文章内容
     * @apiGroup ArtWeb
     * @apiVersion 1.0.0
     * @apiDescription 前端文章详细内容接口

     * @apiParam (请求参数：) {number}     		id 文章ID

     * @apiSuccessExample {json} 成功示例
     * {"code":10200,"msg":"获取数据成功","time":1594969422,"data":{"total":0,"list":[]}}
     * @apiErrorExample {json} 失败示例
     * {"code":10300,"msg":"类型只支持选择1|2|3","time":1594969705,"data":{"apiParam":"error"}}
     */
    public function artContent() {
        $this->valiParam('addRead');
        $model = new \app\model\Article;
        $arr = $model->singArt($this->paramInfo['id']);
        $this->result(['content'=>$arr], 10200, 'success');
    }
}