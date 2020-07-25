<?php
/**
 * +----------------------------------------------------------------------
 * | 后台文章管理接口
 * +----------------------------------------------------------------------
 */
namespace app\controller;

use app\model\Article;
use think\exception\ValidateException;
use think\facade\{Request, Log};
use app\facade\Qiniu;

class ArtAdmin extends Base
{
    /**
     * 控制器中间件 [ 不需要鉴权 ]
     * @var array
     */
    protected $middleware = [
        'app\middleware\Api' => ['except' => ['uploader']],
    ];

    protected $model;

    /**
     * 构造方法
     * @access protected
     */
    public function __construct() {
        $this->model = new Article;
    }
    /**
     * 上传文件
     * @return mixed
     */
    public function uploader() {
        $data_type = Request::param('genre');
        $file = request()->file('file');
        // 上传到本地服务器
        try {
            validate(['file'=>'filesize:10240000|fileExt:doc,docx,xlsx,xls,txt,pdf,jpg,png'])
                ->check(['file'=>$file]);
            $savename = \think\facade\Filesystem::putFile($data_type, $file);
            $savename=str_replace('\\','/',$savename);
            $filename = strrchr($savename,'/');// “/”最后出现的位置
            $filename = substr($filename,1);
            $filename = Qiniu::upload($file, $filename);
            $this->result(['url' => $filename], 1, '上传成功');
        } catch (\think\exception\ValidateException $e) {
            $this->result([], 0, $e->getMessage());
        }
        
    }
    /**
     * @api {post} /ArtAdmin/artList 01、文章列表
     * @apiGroup ArtAdmin
     * @apiVersion 1.0.0
     * @apiDescription 文章列表接口，返回 所需类型文章数组

     * @apiParam (请求参数：) {string}     		type 文章类型

     * @apiParam (响应字段：) {Array}     		list    文章数据
     * @apiParam (响应字段：) {number}     		total   文章总数

     * @apiSuccessExample {json} 成功示例
     * {"code":10200,"msg":"获取数据成功","time":1594969422,"data":{"total":0,"list":[]}}
     * @apiErrorExample {json} 失败示例
     * {"code":10300,"msg":"类型只支持选择1|2|3","time":1594969705,"data":{"apiParam":"error"}}
     */
    public function artList()
    {
        $paramInfo = Request::param();
        try {
            validate(\app\validate\Article::class)
                ->scene('list')
                ->check($paramInfo);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $this->result(['apiParam' => 'error'], 10300, $e->getError());
        }
        $type = $paramInfo['type'];
        $list = $this->model->artInfo($type);
        $total = count($list);
        $this->result(['total' => $total, 'list' => $list], 10200, '获取数据成功');
    }

    /**
     * @api {post} /ArtAdmin/addArt 02、添加文章
     * @apiGroup ArtAdmin
     * @apiVersion 1.0.0
     * @apiDescription 文章新增接口

     * @apiParam (请求参数：) {string}     		pic_url 文章插图地址
     * @apiParam (请求参数：) {string}     		title 文章标题
     * @apiParam (请求参数：) {string}     		content 文章内容
     * @apiParam (请求参数：) {number}     		tag 文章标签
     * @apiParam (请求参数：) {number}     		type 文章类型
     * @apiParam (请求参数：) {string}     		uploader 文章附件地址

     * @apiParam (响应字段：) {number}     		id   新增文章ID

     * @apiSuccessExample {json} 成功示例
     * {"code":10200,"msg":"文章添加成功","time":1594969422,"data":{"id":1}}
     * @apiErrorExample {json} 失败示例
     * 
     */
    public function addArt()
    {
        $paramInfo = Request::param();
        $str = $paramInfo['content'];
        $str1 = str_replace('spellcheck="false">','spellcheck="false"><code>',$str);
        $str2 = str_replace('</pre>','</code></pre>',$str1);
        $data = array(
            'title' => $paramInfo['title'],
            'pic_url' => $paramInfo['pic_url'],
            'content' => $str2,
            'tag' => implode(',', $paramInfo['tags']),
            'type' => $paramInfo['type'],
            'read_num' => 0,
            'like_num' => 0,
            'isdel' => 0,
            'uid' => $this->getUid(),
            'uploader' => isset($paramInfo['uploader'])? $paramInfo['uploader'] : ''
        );
        try {
            validate(\app\validate\Article::class)
                ->scene('add')
                ->check($data);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $this->result(['apiParam' => 'error'], 10300, $e->getError());
        }
        $row = $this->model->addArt($data);
        $logInfo = array(
            'uid' => $this->getUid(),
            'addID' => $row,
            'time' => time()
        );
        Log::write($logInfo, 'info');
        $this->result(['id' => $row], 10200, '文章添加成功');
    }

    /**
     * @api {post} /ArtAdmin/delArt 03、删除文章
     * @apiGroup ArtAdmin
     * @apiVersion 1.0.0
     * @apiDescription 文章软删除接口

     * @apiParam (请求参数：) {number}     		id 文章ID

     * @apiSuccessExample {json} 成功示例
     * {"code":10200,"msg":"文章删除成功","time":1594974058,"data":[]}
     * @apiErrorExample {json} 失败示例
     * {"code":10400,"msg":"文章删除错误","time":1594974058,"data":[]}
     */
    public function delArt()
    {
        $paramInfo = Request::param();
        try {
            validate(\app\validate\Article::class)
                ->scene('del')
                ->check($paramInfo);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $this->result(['apiParam' => 'error'], 10300, $e->getError());
        }
        $row = $this->model->delArt($paramInfo['id']);
        $logInfo = array(
            'uid' => $this->getUid(),
            'delID' => $paramInfo['id'],
            'time' => time(),
            'isSuc' => $row
        );
        Log::write($logInfo, 'info');
        if ($row) {
            $this->result([], 10200, '文章删除成功');
        } else {
            $this->result([], 10400, '文章删除错误');
        }
        
    }

    /**
     * @api {post} /ArtAdmin/editArt 04、修改文章
     * @apiGroup ArtAdmin
     * @apiVersion 1.0.0
     * @apiDescription 文章修改接口

     * @apiParam (请求参数：) {number}     		id 文章ID
     * @apiParam (请求参数：) {string}     		pic_url 文章插图地址
     * @apiParam (请求参数：) {string}     		title 文章标题
     * @apiParam (请求参数：) {string}     		content 文章内容
     * @apiParam (请求参数：) {number}     		tag 文章标签
     * @apiParam (请求参数：) {number}     		type 文章类型
     * @apiParam (请求参数：) {string}     		uploader 文章附件地址

     * @apiSuccessExample {json} 成功示例
     * {"code":10200,"msg":"文章删除成功","time":1594974058,"data":[]}
     * @apiErrorExample {json} 失败示例
     * {"code":10400,"msg":"文章删除错误","time":1594974058,"data":[]}
     */
    public function editArt()
    {
        $paramInfo = Request::param();
        $data = array(
            'id' => $paramInfo['id'],
            'title' => $paramInfo['title'],
            'pic_url' => $paramInfo['pic_url'],
            'content' => $paramInfo['content'],
            'tag' => implode(',', $paramInfo['tags']),
            'type' => $paramInfo['type'],
            'uploader' => isset($paramInfo['uploader'])? $paramInfo['uploader'] : ''
        );
        try {
            validate(\app\validate\Article::class)
                ->scene('edit')
                ->check($data);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $this->result(['apiParam' => 'error'], 10300, $e->getError());
        }
        
        $row = $this->model->artEdit($data);
        $logInfo = array(
            'uid' => $this->getUid(),
            'editID' => $paramInfo['id'],
            'time' => time(),
            'isSuc' => $row
        );
        Log::write($logInfo, 'info');
        if ($row) {
            $this->result([], 10200, '文章修改成功');
        } else {
            $this->result([], 10400, '文章修改错误');
        }
        
    }
}
