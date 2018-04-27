<?php
namespace app\index\controller;
use app\admin\model\Administrators;
use think\facade\Request;
use think\captcha\Captcha;
use app\util\Tools;
use app\util\ReturnCode;
use think\Validate;
class Login extends Base
{
    /**
     * 登录处理
     * @return json
     */
    public function index()
    {
        $input = Request::param();
        // 获取客户端设备
        $agent = Request::header('User-Agent');
        // 跨域不验证验证码，验证码（seesion）暂时无法实现跨域
        if( $this->isDomain == 0){
            $rule = [
                //管理员登陆字段验证
                'users|管理员账号' => 'require|min:5',
                'pwd|管理员密码'   => 'require|min:5',
            ];
            $res = $this->verification($rule, $input, $agent);
        }else {
            $rule = [
                //管理员登陆字段验证
                'users|管理员账号' => 'require|min:5',
                'pwd|管理员密码'   => 'require|min:5',
                'verify|验证码'    => 'require|length:4',
            ];
            $captcha = new Captcha();
            if( array_key_exists('verify', $input) && !$captcha->check($input['verify'])){
                $res = array(ReturnCode::VERIFICATIONFAILURE, '验证码错误！');
            }else {
                $res = $this->verification($rule, $input, $agent);
                $res[2] = [];
            }
        }
        return $this->buildReturn($res);
    }


    private function verification($rule, $input, $agent)
    {
        $user = new Administrators;
        // 约束登录次数
        $attack = [];
        $cname = isset($input["users"]) ? $input["users"] : '';
        $pwd = isset($input["pwd"]) ? $input["pwd"] : '';
        if( cache($cname) != null){
            $attack = cache($cname);
        }
        // 数据验证
        $validate = new Validate($rule);
        $result   = $validate->check($input);
        if( !$result){
            $res = array(ReturnCode::VERIFICATIONFAILURE, $validate->getError());
            array_push($attack, time());
        }else {
            $preview = $user->where(array(
                'users'=> $cname
            ))->find();
            if( !$preview){
                $res = array(ReturnCode::NODATA, Tools::errorCode(ReturnCode::NODATA));
                array_push($attack, time());
            }else if( $preview['status'] == 1){
                $res = array(ReturnCode::LOCKACCOUNT, Tools::errorCode(ReturnCode::LOCKACCOUNT));
            }else {
                $where_query = array(
                    'users' => $cname,
                    'pwd'   => $pwd
                );
                if( $user = $user->where($where_query)->find()) {
                    //更新最后请求IP及时间
                    $time = date('Y-m-d H:i:s', time());
                    // 加密账户密码
                    $salt = md5($user->users.$user->pwd);
                    // 对数据二次加密
                    $token = encryption($user->id, $agent, $salt);
                    // 更新时间
                    $user->where($where_query)->update(['lasttime' => $time]);
                    if( $input['online'] == '1'){
                        // 标识存入cookie
                        cookie("identity", $token, 3600*24*30);
                    }else if( $input['online'] == '0') {
                        // 标识存入cookie
                        cookie("identity", $token,  3600*12);
                    }
                    // 返回状态
                    $res = array(ReturnCode::SUCCESS, Tools::errorCode(ReturnCode::SUCCESS), $token);
                } else {
                    $res = array(ReturnCode::AUTH_ERROR, Tools::errorCode(ReturnCode::AUTH_ERROR));
                    array_push($attack, time());
                }
            }
        }
        if( cache($cname) != null){
            if( count($attack) > 10){
                // 锁当前用户
                Administrators::where('users', 'EQ', $cname)->update(array('status' => 1));
                cache($cname,  NULL);
                // 返回状态
                $res = array(ReturnCode::LOCKACCOUNT, Tools::errorCode(ReturnCode::LOCKACCOUNT));
            }else {
                // 取出第一个时间戳
                $start = $attack[0];
                // 计算还剩余多少时间
                $interval = 3600-(time()-$start);
                // 清除原有缓存，重新存储
                cache($cname,  NULL);
                cache($cname, $attack, $interval);
            }
        }else {
            // 第一次就存第一个时间戳
            cache($cname, $attack, 3600);
        }
        return $res;
    }


    /**
     * 退出系统
     * @return json
     */
    public function secede()
    {
        if( $this->AuthPermission == '200'){
            cookie('identity', null);
            $res = array(ReturnCode::SUCCESS, Tools::errorCode(ReturnCode::SUCCESS));
        }else {
            $res = $this->returnRes($this->AuthPermission, 'true');
        }
        return $this->buildReturn($res);
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

    public function logint()
    {
        return $this->fetch('Login/logint');
    }
}
