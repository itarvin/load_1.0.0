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
    public function index()
    {
        $input = Request::param();
        $user = new Administrators;
        $rule = [
            //管理员登陆字段验证
            'users|管理员账号' => 'require|min:5',
            'pwd|管理员密码'   => 'require|min:5',
            'verify|验证码'    => 'require|length:4',
        ];
        // 先验证验证码是否正确
        $captcha = new Captcha();
        if(array_key_exists('verify',$input) && !$captcha->check($input['verify'])){
            $data['status'] = ReturnCode::VERIFICATIONFAILURE;
            $data['info'] = '验证码错误！';
        }else{
            // 数据验证
            $validate = new Validate($rule);
            $result   = $validate->check($input);
            if(!$result){
                $data['status'] = ReturnCode::VERIFICATIONFAILURE;
                $data['info'] = $validate->getError();
            }else{
                $preview = $user->where(array(
                    'users'=>$input['users']
                ))->find();

                if(!$preview){
                    $data['status'] = ReturnCode::NODATA;
                    $data['info'] = Tools::errorCode(ReturnCode::NODATA);
                }
                $where_query = array(
                    'users' => $input['users'],
                    'pwd' => $input['pwd'],
                );
                if ($user = $user->where($where_query)->find()) {
                    //更新最后请求IP及时间
                    $time = date('Y-m-d H:i:s',time());
                    // 加密账户密码
                    $salt = md5($user->users.$user->pwd);
                    // 获取客户端设备
                    $agent = Request::header('User-Agent');
                    // 对数据二次加密
                    $token = encryption($user->id,$agent,$salt);
                    // 更新时间
                    $user->where($where_query)->update(['lasttime' => $time]);
                    if($input['online'] == 1){
                        // 标识存入cookie
                        setcookie("identity",$token, time()+3600*24*30);
                    }else if($input['online'] == 0) {
                        // 标识存入cookie
                        setcookie("identity",$token, time()+3600*12);
                    }
                    // 返回状态
                    $data['status'] = ReturnCode::SUCCESS;
                    $data['info'] = Tools::errorCode(ReturnCode::SUCCESS);
                } else {
                    $data['status'] = ReturnCode::AUTH_ERROR;
                    $data['info'] = Tools::errorCode(ReturnCode::AUTH_ERROR);
                }
            }
        }
        return json($data);
    }

    // 退出系统
    public function secede()
    {
        if($this->AuthPermission == '200'){
            setcookie('identity', NULL);
            $data = array(
                'status' => ReturnCode::SUCCESS,
                'info'   => Tools::errorCode(ReturnCode::SUCCESS)
            );
        }else {
            $data['status'] = ReturnCode::AUTH_ERROR;
            $data['info'] = Tools::errorCode(ReturnCode::AUTH_ERROR);
        }
        return json($data);
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
}
