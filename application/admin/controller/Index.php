<?php
namespace app\admin\controller;
/**
 * 首页基本类
 * @author  itarvin itarvin@163.com
 */
use app\model\Privilege;
use app\model\Role;
use app\model\Adminrole;
class Index extends Base
{
    public function index()
    {
        // 取出权限列表
        $priModel = new Privilege;
        $btns = $priModel->getBtns();

        // 取出当前管理员所在的角色ID
        $arModel = new Adminrole;
        $roleId = $arModel->field('GROUP_CONCAT(role_id) role_id')->where('admin_id', 'eq', $this->uid)->find();

        // 拿到角色信息放到页面上
        $roleModel = new Role;
        $role_user = $roleModel->field('role_name')->where('id',$roleId['role_id'])->find();
        
        $this->assign([
            'name'    => $this->name,
            'uid'     => $this->uid,
            'role_name' => $role_user['role_name'],
            'btns' => $btns,
        ]);
        return $this->fetch('Index/index');
    }

    public function welcome()
    {
        return $this->fetch('Index/welcome');
    }
}
