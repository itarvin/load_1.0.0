<?php
namespace app\api\controller;
/**
 * 空控制器
 * @author  itarvin itarvin@163.com
 * @return json
 */
use think\facade\Request;
use think\Controller;
class Error extends Controller
{
    public function index(Request $request)
    {
        return json(array(
            'status' => '404',
            'info' => '你想要的操作对象！程序员小哥哥没找到！(╯﹏╰)'
        ));
    }
}
