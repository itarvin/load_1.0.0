<?php
namespace app\admin\controller;
/**
 * 应用场景：首页基本类
 * @author  itarvin itarvin@163.com
 */
use app\model\Privilege;
use app\model\Role;
use app\model\Adminrole;
use app\model\Admin;
use app\model\Member;
use app\model\Record;
class Index extends Base
{
    /**
     * 应用场景：首页,导航权限,角色信息
     * @return array
     */
    public function index()
    {
        // 取出权限列表
        $priModel = new Privilege;
        $btns = $priModel->getBtns();
        // 取出当前管理员所在的角色ID
        $arModel = new Adminrole;
        $roleId = $arModel->field('GROUP_CONCAT(role_id) role_id')->where('admin_id', $this->uid)->find();
        // 拿到角色信息放到页面上
        $roleModel = new Role;
        $role_user = $roleModel->field('role_name')
        ->where('id',$roleId['role_id'])
        ->find();
        $this->assign([
            'name'      => $this->name,
            'uid'       => $this->uid,
            'role_name' => $role_user['role_name'],
            'btns'      => $btns,
        ]);
        return $this->fetch('');
    }


    /**
     * 应用场景：欢迎页，统计信息
     * @return array
     */
    public function welcome()
    {
        $today = $this->getCount('d',$this->uid);
        $yesterday = $this->getCount('yesterday',$this->uid);
        $week = $this->getCount('w',$this->uid);
        $month = $this->getCount('m',$this->uid);
        $all = $this->getCount('',$this->uid);
        $this->assign([
            'today'     => $today,
            'yesterday' => $yesterday,
            'week'      => $week,
            'month'     => $month,
            'all'       => $all
        ]);
        return $this->fetch('');
    }


    /**
     * 应用场景：获取对应的日期统计
     * @return array
     */
    private function getCount($time = '', $uid)
    {
        $where = [];
        $map = [];
        if($time != ''){
            if(!checksuperman($uid)){
                $map[] = ['uid', 'EQ', $uid];
            }
            $data['member'] = Member::whereTime('newtime',$time)->where($map)->count();
            $data['record'] = Record::whereTime('newtime',$time)->where($map)->count();
            $allRecord = Record::field('price')->whereTime('newtime',$time)->where($map)->select();
            $mark = 0;
            foreach ($allRecord as $key => $value) {
                $mark += $value['price'];
            }
            $data['mark'] = $mark;
            $data['admin'] = Admin::whereTime('newtime',$time)->count();
            return $data;
        }else {
            if(!checksuperman($uid)){
                $map[] = ['uid', 'EQ', $uid];
            }
            $data['admin'] = Admin::count();
            $data['member'] = Member::where($map)->count();
            $data['record'] = Record::where($map)->count();
            $allRecord = Record::field('price')->where($map)->select();
            $mark = 0;
            foreach ($allRecord as $key => $value) {
                $mark += $value['price'];
            }
            $data['mark'] = $mark;
            return $data;
        }
    }
}
