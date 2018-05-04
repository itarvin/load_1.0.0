<?php
namespace app\admin\controller;
/**
 * 后台登录类
 * @author  itarvin itarvin@163.com
 */
use think\Controller;
use app\model\Admin;
use think\facade\Cookie;
use think\Validate;
use think\captcha\Captcha;
class  Login extends controller
{
    /**
     * 登录静态页
     */
    public function login()
    {
        return $this->fetch('Login/login');
    }

    /**
     * 验证码输出
     * @return img
     */
    public function verify()
    {
        $config = [
            // 验证码字体大小
            'fontSize'    =>   20,
            // 验证码位数
            'length'      =>   4,
            // 关闭验证码杂点
            'useNoise'    =>   false,
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }

    /**
     * 处理数据登录
     */
    public function do_login()
    {
        $user = new Admin;
        $data = input('post.');
        // 先验证验证码是否正确
        if( !captcha_check($data['verify'])){
            $this->error('验证码错误！', 'Login/login');
        }
        $rule = [
            //管理员登陆字段验证
            'users|管理员账号' => 'require',
            'pwd|管理员密码' => 'require',
        ];
        // 数据验证
        $validate = new Validate($rule);
        $result   = $validate->check($data);
        if( !$result){
            $this->error($validate->getError(), 'Login/login');;
        }
        $preview = $user->where(array(
            'users' => $data['users']
        ))->find();
        if( !$preview){
            $this->error('当前用户不存在', 'Login/login');
        }
        $where_query = array(
            'users' => $data['users'],
            'pwd' => $data['pwd'],
        );
        if( $user = $user->where($where_query)->find()) {
            //注册session
            $sid = base64_encode(json_encode($user->id));
            session('uid', $sid);
            session('u_name', $user->users);
            $salt = md5($user->users.$user->pwd);
            // 设置cookie 前缀为think_
            Cookie::set('auth_'.md5($sid), $salt, ['expire'=> 3600 * 12 ]);
            //更新最后请求IP及时间
            $time = date('Y-m-d H:i:s', time());
            $user->where($where_query)->update(['lasttime' => $time]);
            return $this->success('登录成功', 'Index/index');
        } else {
            $this->error('登录失败:账号或密码错误', 'Login/login');
        }
    }


    /**
     * 退出
     */
    public function logout()
    {
        $request = request();
        session(null);
        return $this->success('已成功登出', 'Login/login');
    }


    /**
     * 空操作返回状态
     * @return json
     */
    public function _empty($name)
    {
        return json(array(
            'status' => '404',
            'info' => '你想要的操作对象！程序员小哥哥没找到！(╯﹏╰)'
        ));
    }
}
