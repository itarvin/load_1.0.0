<?php
/**
 * 后台操作日志记录
 * @since   2018-04-10
 * @author  itarvin <itarvin@163.com>
 */

namespace app\admin\behavior;


use app\model\Logact;
use app\model\Logs;
use app\util\ReturnCode;
use think\facade\Request;

class AdminLog {
    /**
     * 后台操作日志记录
     * @author itarivn <itarivn@163.com>
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function run() {
        // $request = new Request;
        // // $route = $request->routeInfo();
        // var_dump($request);die;
    }
}
