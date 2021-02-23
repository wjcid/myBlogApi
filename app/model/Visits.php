<?php
/**
 * +----------------------------------------------------------------------
 * | 访问记录数据模型
 * +----------------------------------------------------------------------
 */
namespace app\model;
use think\Model;
use think\facade\Cache;

class Visits extends Model
{
  // 浏览总量
  public function pv() {

  }

  // 访问者总数
  public function totVisitor() {

  }

  // 移动端、PC端各总数
  public function pcOrMob() {

  }

  // 各时间点访问数量
  public function timeGroup() {
    
  }
}
