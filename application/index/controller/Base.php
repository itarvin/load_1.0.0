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
        // 取回cookie 信息
        $token = $_COOKIE['identity'];
        // 获取客户端设备
        $agent = Request::header('User-Agent');
        // 析出用户id和客户信息
        $result = analysisCode($token);
        // 解析token验证
        $key = json_decode(base64_decode($result['token'], true),true);
        $uid = json_decode(base64_decode($result['uid']));
        $preview = Administrators::field('users,pwd')->find($uid);
        $this->uid = $uid;
        // 验证是否当前用户设备与提交的用户设备一致.
        if($key['agent'] == $agent && md5($preview['users'].$preview['pwd']) == $key['salt']){
            $this->AuthPermission = '200';
        }else {
            // 验证全局标识是否合法,可能被盗用cookie信息,清除身份信息
            if(md5($preview['users'].$preview['pwd']) == $key['salt']){
                $this->AuthPermission = '300';
                setcookie('identity', NULL);
            }else {
                $this->AuthPermission = '400';
                setcookie('identity', NULL);
            }
        }
    }
}
