<?php
namespace app\index\controller;
/**
 * 接口基类
 * @since   2018/04/25 创建
 * @author  itarvin itarvin@163.com
 */
use think\Controller;
use app\admin\model\Administrators;
use think\facade\Request;
use think\facade\Cookie;
use app\util\ReturnCode;
class Base extends Controller
{
    /**
     * 初始化方法
     */
    protected function initialize(){
        // 获取配置允许域名
        $allow_origin = config('TRENDS_ALLOW_ORIGIN');
        $origins = isset($_SERVER['HTTP_ORIGIN']);
        // 跨域验证
        if(is_array($allow_origin))
        {
            // 获取请求来源域
            $origin = $origins ? $_SERVER['HTTP_ORIGIN'] : '';
            if(in_array($origin, $allow_origin)){
                header('Access-Control-Allow-Origin:'.$origin);
            }
        }else if(is_string($allow_origin)){
            header('Access-Control-Allow-Origin:'.$allow_origin);
        }
        // 请求头
        header('Access-Control-Allow-Methods:POST,GET');
        header('Access-Control-Allow-Headers:Token,Cookie,X-Requested-With,User-Agent');
        if($origins){
            // 分割地址和协议
            $url = parse_url($_SERVER['HTTP_ORIGIN']);
            // 跨域标识
            if($url['host'] == $_SERVER['HTTP_HOST']){
                $this->isDomain = '1';
            }else {
                $this->isDomain = '0';
            }
        }
        // 校验是否存在当前cookie
        if(Cookie::has('identity') || Request::header('token')){
            if(Request::header('token')){
                $token = Request::header('token');
            }else {
                $token = cookie('identity');
            }
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
                    cookie('identity',null);
                }else {
                    $this->AuthPermission = '400';
                    cookie('identity',null);
                }
            }
        }else {
            // echo '451652';die;
            $this->AuthPermission = '400';
        }
    }


    /**
     * 统一接口输出值
     * @param $res
     * @return json
     */
    public function buildReturn($res) {
        $return = [
            'status' => $res[0],
            'info'  => $res[1],
            'data' => isset($res[2]) ? $res[2] : []
        ];
        return json($return);
    }


    /**
     * 由状态值返回对应对应回应
     * @param $res
     * @return array
     */
    public function returnRes($AuthPermission,$isCheck = 'false')
    {
        if($isCheck == 'true'){
            if($AuthPermission == '400'){
                $res = array(ReturnCode::ACCOUNTEXPIRED,"I'm glad to meet you（终于等到你！）");
            }else if($AuthPermission == '300'){
                $res = array(ReturnCode::ACCOUNTEXPIRED,Tools::errorCode(ReturnCode::ACCOUNTEXPIRED));
            }
        }
        return $res;
    }
}
