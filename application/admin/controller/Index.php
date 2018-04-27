<?php
namespace app\admin\controller;
class Index extends Base
{
    public function index()
    {
        $this->assign([
            'name'    => $this->name,
            'uid'     => $this->uid,
            'issuper' => $this->superman
        ]);
        return $this->fetch('Index/index');
    }

    public function welcome()
    {
        return $this->fetch('Index/welcome');
    }
}
