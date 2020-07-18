<?php
/**
 * +----------------------------------------------------------------------
 * | 用户管理模型
 * +----------------------------------------------------------------------
 */
namespace app\model;
use think\Model;

class Users extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    //用户信息
    public function userInfo($status) {
        $list = Users::where('status', $status)->order('id', 'asc')->select();
        return $list;
    }

    //用户信息修改 data中需带ID
    public function editInfo($data) {
        $row = Users::update($data);
        return $row;
    }

    //用户权限查询
    public function permissionInfo($uid) {
        $list = Users::where('id', $uid)->value('permission');;
        return $list;
    }

    //用户权限修改
    public function permissionEdit($uid, $permission) {
        $user = Users::find($uid);
        $user->permission = $permission;
        $row = $user->save();
        return $row;
    }

    //删除用户
    public function delUser($uid) {
        $row = Users::destroy($uid);
        //$user = Users::find($uid);
        //$user->isdel = '0';
        //$user->save();
        return $row;
    }

    //更新用户状态
    public function setStauts($uid, $status) {
        $user = Users::find($uid);
        $user->status = $status;
        $row = $user->save();
        return $row;
    }

    //获取单个用户信息
    public function singUser($uid) {
        $data = Users::find($uid);
        $arr['name'] = $data->name;
        $arr['phone'] = $data->phone;
        $arr['email'] = $data->email;
        $arr['unit'] = $data->unit;
        return $arr;
    }
}