<?php
namespace app\admin\controller;
/**
 * 角色类
 * @author  itarvin itarvin@163.com
 */
use app\model\Role;
use think\facade\Request;
use app\Util\ReturnCode;
use app\Util\Tools;
class Roles extends Base
{
    /**
     * 角色主页
     * @return array
     */
    public function index()
    {
        return $this->fetch('Roles/index');
    }


    /**
     * 角色添加
     * @return array
     */
    public function add()
    {
        return $this->fetch('Roles/add');
    }
}
