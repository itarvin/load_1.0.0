<?php
namespace app\admin\controller;
/**
 * app基类
 * @since   2018/03/17 创建
 * @author  itarvin itarvin@163.com
 */
use think\Controller;
use app\common\model\Admin;
class Base extends Controller
{
    // 初始化方法
    protected function initialize(){
        // 解密session, 对比权限
        $this->uid = json_decode(base64_decode(session('uid'),  true), true);
        if( array_key_exists('auth_'.md5(session('uid')), $_COOKIE)){
            $auth = $_COOKIE['auth_'.md5(session('uid'))] ? $_COOKIE['auth_'.md5(session('uid'))] : '';
            if( $auth){
                $user = Admin::field('users, pwd')->find($this->uid);
                if( md5($user['users'].$user['pwd']) != $auth){
                    $this->redirect(url('Login/login'));
                }
            }else {
                $this->redirect(url('Login/login'));
            }
        }else {
            $this->redirect(url('Login/login'));
        }
        $this->name = session('u_name');
        if( $this->checksuperman()){
            $this->superman = 'yes';
        }else {
            $this->superman = 'no';
        }
    }

    /**
     * 检测是否当前用户是超管
     * @return Boole
     */
    protected function checksuperman()
    {
        $superlist = config('IS_SUPERMAN');
        if( in_array($this->uid, $superlist)){
            return true;
        }else {
            return false;
        }
    }
}
