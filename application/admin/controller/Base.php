<?php
namespace app\admin\controller;
/**
 * app基类
 * @since   2018/03/17 创建
 * @author  itarvin itarvin@163.com
 */
use think\Controller;
use app\model\Admin;
use app\model\Privilege;
class Base extends Controller
{
    // 初始化方法
    protected function initialize(){

        if(!session('uid'))
        {
            $this->redirect(url('Login/login'));
        }
        // 解密session, 对比权限
        $this->uid = json_decode(base64_decode(session('uid'),  true), true);

        if( array_key_exists('auth_'.md5(session('uid')), $_COOKIE)){

            $auth = $_COOKIE['auth_'.md5(session('uid'))] ? $_COOKIE['auth_'.md5(session('uid'))] : '';

            if( $auth){

                $user = Admin::field('users,pwd')->find($this->uid);

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
        // 所有管理员都可以进入后台的首页
        if(request()->controller() == 'Index'){
            return TRUE;
        }
        $priModel = new Privilege;

        if(!$priModel->checkPri()){
            $this->error('你想要的操作对象！程序员小哥哥办不到！(╯﹏╰)');
        }
    }


    /**
     * 空操作返回状态
     * @return json
     */
    public function _empty($name)
    {
        return json([
            'status' => '404',
            'info' => '你想要的操作对象！程序员小哥哥没找到！(╯﹏╰)'
        ]);
    }
}
