<?php
/**
 * +----------------------------------------------------------------------
 * | 文章管理模型
 * +----------------------------------------------------------------------
 */
namespace app\model;
use think\Model;
use think\facade\Cache;

class Article extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
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
    // 文章信息
    public function artInfo($type) {
        $list = Article::where('type', $type)->where('isdel', 0)->order('id', 'desc')->select();
        return $list;
    }

    // 文章信息修改 data中需带ID
    public function artEdit($data) {
        $art = Article::find($data['id']);
        $artCache = array(
            'id' => $data['id'],
            'title' => $data['title'],
            'pic_url' => $data['pic_url'],
            'tag' => $data['tag'],
            'create_time' => substr($art['create_time'], 0, 10)
        );
        $key = $this->cacheKey($art['type']);
        $artkey = $key['artkey'].$data['id'];
        // 将数据写入hash缓存
        Cache::hmset($artkey, $artCache);
        Article::update($data);
        return true;
    }

    // 删除文章
    public function delArt($id) {
        //$row = Article::destroy($id);
        $art = Article::find($id);
        if(empty($art)){
            return false;
        }
        $art->isdel = '1';
        $row = $art->save();
        if($row) {
            // 将ID从集合移除
            Cache::srem('artid:'.$art['type'], $art['id']);
            // 移除hash缓存
            $key = $this->cacheKey($art['type']);
            $artkey = $key['artkey'].$art['id'];
            Cache::del($artkey);
            // 移出排行榜
            Cache::zrem($key['rankkey'], $art['id']);
        }
        return $row;
    }

    // 获取单篇文章信息
    public function singArt($id) {
        $data = Article::find($id);
        $arr['title'] = $data->title;
        $arr['pic_url'] = $data->pic_url;
        $arr['content'] = $data->content;
        $arr['tag'] = $data->tag;
        $arr['read_num'] = $data->read_num;
        return $arr;
    }
    //查询文章上下文
    public function udArt($id) {
        
    }
    // 添加文章
    public function addArt($data) {
        $info = Article::create($data);
        $artcache = array(
            'id' => $info->id,
            'title' => $data['title'],
            'pic_url' => $data['pic_url'],
            'tag' => $data['tag'],
            'create_time' => date("Y-m-d")
        );
        $key = $this->cacheKey($data['type']);
        $artkey = $key['artkey'].$info->id;
        // 将ID写入集合
        Cache::sadd('artid:'.$data['type'], $info->id);
        // 将数据写入hash缓存
        Cache::hmset($artkey, $artcache);
        // 写入排行榜有序集合
        Cache::zadd($key['rankkey'], 0, $info->id);
        return $info->id;
    }
}