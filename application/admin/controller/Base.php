<?php
namespace app\admin\controller;

/**
 * app基类
 * @since   2018/03/17 创建
 * @author  itarvin itarvin@163.com
 */
use think\Controller;

class Base extends Controller
{
    // 初始化方法
    protected function initialize(){
        $this->uid = session('uid');
        $this->name = session('u_name');
        // 继承
        if (!$this->uid) {
            $this->redirect(url('Backdoor/login'));
        }
    }
}
