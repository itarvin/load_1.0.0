<?php
namespace app\admin\controller;

/**
 * app基类
 * @since   2018/03/17 创建
 * @author  itarvin itarvin@163.com
 */
use think\Controller;
use app\admin\model\Administrators;
class Base extends Controller
{
    // 初始化方法
    protected function initialize(){
        // 解密session,对比权限
        $this->uid = json_decode(base64_decode(session('uid'), true),true);
        if(array_key_exists('load_auth_'.md5(session('uid')),$_COOKIE)){
            $auth = $_COOKIE['load_auth_'.md5(session('uid'))] ? $_COOKIE['load_auth_'.md5(session('uid'))] : '';
            if($auth){
                $user = Administrators::field('users,pwd')->find($this->uid);
                if(md5($user['users'].$user['pwd']) != $auth){
                    $this->redirect(url('Backdoor/login'));
                }
            }else {
                $this->redirect(url('Backdoor/login'));
            }
        }else {
            $this->redirect(url('Backdoor/login'));
        }
        $this->name = session('u_name');
    }
}
