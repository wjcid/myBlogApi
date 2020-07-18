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
                $artKey = 'developArt:';
                $rankkey = 'derank';
                break;
            case 2:
                $artKey = 'readArt:';
                $rankkey = 'rerank';
                break;
            case 3:
                $artKey = 'liveArt:';
                $rankkey = 'lirank';
                break;
        }
        $key = array(
            'artKey' => $artKey,
            'rankkey' => $rankkey
        );
        return $key;
    }
    // 文章信息
    public function artInfo($type) {
        $list = Article::where('type', $type)->order('id', 'desc')->select();
        return $list;
    }

    // 文章信息修改 data中需带ID
    public function artEdit($data) {
        $art = Article::find($data['id']);
        if(empty($art)){
            return false;
        }
        $data = array(
            'id' => $art->id,
            'title' => $art->title,
            'pic_url' => $art->pic_url,
            'tag' => $art->tag,
            'create_time' => $art->create_time
        );
        $key = $this->cacheKey($art->type);
        $artkey = $key['artkey'].$art->id;
        // 将数据写入hash缓存
        Cache::hmset($artkey, $data);
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
            Cache::srem('artid:'.$art->type, $art->id);
            // 移除hash缓存
            $key = $this->cacheKey($art->type);
            $artkey = $key['artkey'].$art->id;
            Cache::del($artkey);
            // 写入排行榜有序集合
            Cache::zrem($key['rankkey'], $art->id);
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

    // 添加文章
    public function addArt($data) {
        $info = Article::create($data);
        $data = array(
            'id' => $info->id,
            'title' => $info->title,
            'pic_url' => $info->pic_url,
            'tag' => $info->tag,
            'create_time' => $info->create_time
        );
        $key = $this->cacheKey($info->type);
        $artkey = $key['artkey'].$info->id;
        // 将ID写入集合
        Cache::sadd('artid:'.$info->type, $info->id);
        // 将数据写入hash缓存
        Cache::hmset($artkey, $data);
        // 写入排行榜有序集合
        Cache::zadd($key['rankkey'], 0, $info->id);
        return $info->id;
    }
}