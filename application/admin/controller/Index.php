<?php
namespace app\admin\controller;
class Index extends Base
{
    public function index()
    {
        $this->assign(array(
            'name' => $this->name,
            'uid'  => $this->uid,
            'issuper' => $this->superman
        ));
        return $this->fetch();
    }

    public function welcome()
    {
        return $this->fetch();
    }
}
