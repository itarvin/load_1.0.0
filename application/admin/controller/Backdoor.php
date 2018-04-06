<?php
namespace app\admin\controller;
use think\Controller;
use think\captcha\Captcha;
use app\admin\model\Administrators;
use think\facade\Cookie;
use think\Validate;
class  Backdoor extends controller
{
    public function login()
    {
        return $this->fetch('Backdoor/login');
    }

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


    public function do_login()
    {
        $user = new Administrators;
        $data = input('post.');
        // 先验证验证码是否正确
        $captcha = new Captcha();
        if( !$captcha->check($data['verify']))
        {
        	// 验证失败
            $this->error('验证码错误！','Backdoor/login');
        }
        $rule = [
            //管理员登陆字段验证
            'users|管理员账号' => 'require|min:5',
            'pwd|管理员密码' => 'require|min:5',
        ];
        // 数据验证
        $validate = new Validate($rule);
        $result   = $validate->check($data);
        if(!$result){
            return $validate->getError();
        }
        $preview = $user->where(array(
            'users'=>$data['users']
        ))->find();
        if(!$preview){
            $this->error('当前用户不存在','Backdoor/login');
        }
        $where_query = array(
            'users' => $data['users'],
            'pwd' => $data['pwd'],
        );
        if ($user = $user->where($where_query)->find()) {
            //注册session
            session('uid',$user->id);
            session('u_name',$user->users);
            $salt = md5($user->users.$user->pwd);
            // 设置cookie 前缀为think_
            Cookie::set('auth_'.$user->id,$salt,['prefix'=>'load_','expire'=>3600 * 12]);
            //更新最后请求IP及时间
            $time = date('Y-m-d H:i:s',time());
            $user->where($where_query)->update(['lasttime' => $time]);
            return $this->success('登录成功', 'Index/index');
        } else {
            $this->error('登录失败:账号或密码错误','Backdoor/login');
        }
    }
    // 退出
    public function logout()
    {
        $request = request();
        session(null);
        return $this->success('已成功登出', 'Backdoor/login');
    }
}
