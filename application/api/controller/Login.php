<?php
namespace app\api\controller;
use app\common\model\Admin;
use think\facade\Request;
use think\facade\Cookie;
use think\captcha\Captcha;
use app\util\Tools;
use app\util\ReturnCode;
use think\Validate;
use think\Controller;
class Login extends Controller
{
    /**
     * 登录处理
     * @return json
     */
    public function login()
    {
        $input = Request::param();
        // 获取客户端设备
        $agent = Request::header('User-Agent');
        if( !captcha_check($input['verify'] ))
        {
            return buildReturn(['status' => ReturnCode::SUCCESS, 'info' => '验证码错误！']);
        }
        $rule = [
            //管理员登陆字段验证
            'users|管理员账号' => 'require|min:5',
            'pwd|管理员密码'   => 'require|min:5',
        ];
        $user = new Admin;
        $name = $input["users"];
        // 数据验证
        $validate = new Validate($rule);

        if( !$validate->check($input)){
            return buildReturn(['status' => ReturnCode::VERIFICATIONFAILURE,'info'=> $validate->getError()]);
        }

        $preview = $user->where(array(
            'users'=> $name
        ))->find();

        if( !$preview){

            $this->checkLogin($name);
            return buildReturn(['status' => ReturnCode::AUTH_ERROR,'info'=>  Tools::errorCode(ReturnCode::AUTH_ERROR)]);
        }else if( $preview['status'] == 1){

            $this->checkLogin($name);
            return buildReturn(['status' => ReturnCode::LOCKACCOUNT,'info'=>  Tools::errorCode(ReturnCode::LOCKACCOUNT)]);
        }else {

            $where_query = [
                'users' => $name,
                'pwd'   => $input["pwd"]
            ];
            if( $user = $user->where($where_query)->find()) {
                //更新最后请求IP及时间
                $time = date('Y-m-d H:i:s', time());
                // 加密账户密码
                $salt = md5($user->users.$user->pwd);
                // 对数据二次加密
                $token = $this->encryption($user->id, $agent, $salt);
                // 更新时间
                $user->where($where_query)->update(['lasttime' => $time]);

                if( $input['online'] == 1){
                    // 标识存入cookie
                    Cookie::set('identity', $token, ['expire'=> 3600 * 12 * 30 ]);
                }else if( $input['online'] == 0) {
                    Cookie::set('identity', $token, ['expire'=> 3600 * 12]);
                }

                // 返回状态
                return buildReturn(['status' => ReturnCode::SUCCESS,'info'=>  Tools::errorCode(ReturnCode::SUCCESS)]);
            } else {

                $this->checkLogin($name);
                return buildReturn(['status' => ReturnCode::AUTH_ERROR,'info'=>  Tools::errorCode(ReturnCode::AUTH_ERROR)]);
            }
        }
    }

    private function checkLogin($name, $timestamp)
    {
        $timestamp = $timestamp != '' ? $timestamp : time();
        $attack = cache($name) != null ? cache($name) : [];
        array_push($attack, $timestamp);

        if( cache($name) != null){
            if( count($attack) > 2){
                // 锁当前用户
                Admin::where('users', 'EQ', $name)->update(array('status' => 1));
                cache($name,  NULL);
                // 返回状态
                return buildReturn(['status' => ReturnCode::LOCKACCOUNT,'info'=>  Tools::errorCode(ReturnCode::LOCKACCOUNT)]);
            }else {

                // 取出第一个时间戳
                $start = $attack[0];
                // 计算还剩余多少时间
                $interval = 3600-(time()-$start);
                // 清除原有缓存，重新存储
                cache($name,  NULL);
                cache($name, $attack, $interval);
            }
        }else {
             // 第一次就存第一个时间戳
             cache($name, $attack, 3600);
         }
    }

    /**
     * 退出系统
     * @return json
     */
    public function logout()
    {
        cookie('identity', null);
        return buildReturn(['status' => ReturnCode::SUCCESS,'info'=>  Tools::errorCode(ReturnCode::SUCCESS)]);
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


    // 对base64二次加密处理
    private function encryption($userid,$agent,$salt)
    {
        // 密码薄
        $passwordBook = Tools::makeRandom();
        // 加密UID
        $uid = base64_encode(json_encode($userid));
        $tokens = array(
            'agent' => $agent,
            'salt' => $salt
        );
        // 转为base64
        $key = base64_encode(json_encode($tokens));
        // 计算秘钥长度和用户id长度
        $secretLen = strlen($key);
        $uLen = strlen($uid);
        $random = rand(0,25);
        // 生成随机数
        $start = $passwordBook[$random];
        $end = $passwordBook[$uLen];
        // 分隔字符串
        $tokenStart = mb_substr($key, 0, ($secretLen/2), 'utf-8');
        $uidStart = mb_substr($tokenStart, 0, $random, 'utf-8');
        $uidEnd = mb_substr($tokenStart, $random, strlen($tokenStart), 'utf-8');
        // 生成md5一个介质
        $medium = md5('itarvin'.time());
        // 结束后半部
        $tokenEnd = mb_substr($key,($secretLen/2),$secretLen, 'utf-8');
        // 拼装加密新字符串
        $token = $start.$uidStart.$uid.$uidEnd.$medium.$tokenEnd.$end;
        return $token;
    }

}
