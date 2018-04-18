<?php
namespace app\index\controller;
use think\Controller;
use app\admin\model\Administrators;
use think\facade\Request;
class Base extends Controller
{
    // 初始化方法
    protected function initialize(){
        $allow_origin = config('TRENDS_ALLOW_ORIGIN');
        // 跨域验证
        if(is_array($allow_origin))
        {
            $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
            if(in_array($origin, $allow_origin)){
                header('Access-Control-Allow-Origin:'.$origin);
            }
        }else if(is_string($allow_origin)){
            header('Access-Control-Allow-Origin:'.$allow_origin);
        }
        header('Access-Control-Allow-Methods:POST,GET');
        header('Access-Control-Allow-Headers:Token,X-Requested-With');
        // 获取参数
        $token = Request::header('Token');
        // 解析token验证
        $key = json_decode(base64_decode($token, true),true);
        $uid = json_decode(base64_decode($key['sid']));
        $this->uid = $uid;
        $preview = Administrators::field('users,pwd')->find($uid);
        // 验证全局标识
        if(md5($preview['users'].$preview['pwd']) == $key['salt'] && $key['deadline'] >= time()){
            $this->AuthPermission = '200';
        }else {
            $this->AuthPermission = '400';
        }
    }
}
