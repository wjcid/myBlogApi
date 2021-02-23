<?php
/**
 * +----------------------------------------------------------------------
 * | 缓存为空时创建缓存
 * | （更换服务器时使用）
 * +----------------------------------------------------------------------
 */
namespace app\controller;

use think\facade\{Db, Request, Cache};

class CreateCache extends Base
{
    /**
     * 控制器中间件 [ 不需要鉴权 ]
     * @var array
     */
    protected $middleware = [
        'app\middleware\Api' => ['except' => ['ahmset','addIdSet','addRank','ipstr']],
    ];

    //获取key
    protected function cacheKey($type) {
        switch ($type) {
            case 1:
                $artkey = 'developArt:';
                $rankkey = 'derank';
                break;
            case 2:
                $artkey = 'readArt:';
                $rankkey = 'rerank';
                break;
            case 3:
                $artkey = 'liveArt:';
                $rankkey = 'lirank';
                break;
        }
        $key = array(
            'artkey' => $artkey,
            'rankkey' => $rankkey
        );
        return $key;
    }

    /**
     * @api {post} /CreateCache/ahmset 01、记录文章信息
     * @apiGroup CreateCache
     * @apiVersion 1.0.0
     * @apiDescription 

     * @apiParam (请求参数：) {number}           id 文章ID

     * @apiSuccessExample {json} 成功示例
     * {"code":10200,"msg":"获取数据成功","time":1594969422,"data":{"total":0,"list":[]}}
     * @apiErrorExample {json} 失败示例
     * {"code":10300,"msg":"类型只支持选择1|2|3","time":1594969705,"data":{"apiParam":"error"}}
     */
    public function ahmset() {

        $art = Db::table('bl_article')->field('id,title,pic_url,tag,create_time,type,isdel')->where('isdel',0)->select();
        
        $i = 0;
        foreach ($art as $key => $value) {
            $artCache = array(
                'id' => $value['id'],
                'title' => $value['title'],
                'pic_url' => $value['pic_url'],
                'tag' => $value['tag'],
                'create_time' => date('Y-m-d',$value['create_time'])
            );
            $key1 = $this->cacheKey($value['type']);
            $artkey = $key1['artkey'].$value['id'];
            // 将数据写入hash缓存
            Cache::hmset($artkey, $artCache);
            $i++;
        }
        
        return $i;
        
        
    }

    /**
     * @api {post} /ArtWeb/addIdSet 02、添加所有文章ID集合
     * @apiGroup ArtWeb
     * @apiVersion 1.0.0
     * @apiDescription 文章

     * @apiParam (请求参数：) {number}         id 文章ID

     * @apiSuccessExample {json} 成功示例
     * {"code":10200,"msg":"获取数据成功","time":1594969422,"data":{"total":0,"list":[]}}
     * @apiErrorExample {json} 失败示例
     * {"code":10300,"msg":"类型只支持选择1|2|3","time":1594969705,"data":{"apiParam":"error"}}
     */
    public function addIdSet() {
        $art = Db::table('bl_article')->field('id,type')->where('isdel',0)->select();
        $i = 0;
        foreach ($art as $key => $value) {
            // 将ID写入集合
            Cache::sadd('artid:'.$value['type'], $value['id']);
            $i++;
        }
        
        return $i;
        
    }

    /**
     * @api {post} /ArtWeb/artList 02、添加有序集合
     * @apiGroup ArtWeb
     * @apiVersion 1.0.0
     * @apiDescription 前端文章列表接口

     * @apiParam (请求参数：) {string}         type 文章类型

     * @apiSuccessExample {json} 成功示例
     * {"code":10200,"msg":"获取数据成功","time":1594969422,"data":{"total":0,"list":[]}}
     * @apiErrorExample {json} 失败示例
     * {"code":10300,"msg":"类型只支持选择1|2|3","time":1594969705,"data":{"apiParam":"error"}}
     */
    public function addRank() { 
        $art = Db::table('bl_article')->field('id,type,read_num')->where('isdel',0)->select();
        $i = 0;
        foreach ($art as $key => $value) {
            $key1 = $this->cacheKey($value['type']);
            // 写入排行榜有序集合
            Cache::zadd($key1['rankkey'], $value['read_num'], $value['id']);
            $i++;
        }
        
        return $i;
        
        
    }

    public function ipstr() {
        echo long2ip(1898939514);
    }
    
}