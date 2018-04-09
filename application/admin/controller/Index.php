<?php
namespace app\admin\controller;
class Index extends Base
{
    public function index()
    {
        $this->assign('name',$this->name);
        $this->assign('uid',$this->uid);
        return $this->fetch();
    }

    public function welcome()
    {
        return $this->fetch();
    }
}
