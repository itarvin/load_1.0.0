<?php
namespace app\admin\controller;
use app\admin\model\Administrators;

class Admin extends Base
{
    public function index()
    {
        $user = new Administrators;
        $list = $user->order('id asc')->paginate(10);
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch();
    }
}
