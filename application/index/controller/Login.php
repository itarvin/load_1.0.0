<?php
namespace app\index\controller;
use app\admin\model\Administrators;
use think\facade\Request;
use app\util\Tools;
use app\util\ReturnCode;
use think\Validate;
class Login extends Base
{
    public function index()
    {
        $input = input('post.');
        $user = new Administrators;
        $rule = [
            //管理员登陆字段验证
            'users|管理员账号' => 'require|min:5',
            'pwd|管理员密码' => 'require|min:5',
        ];
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
                $sid = base64_encode(json_encode($user->id));
                $salt = md5($user->users.$user->pwd);
                // UID，加密账户密码。过期时间
                $token = array(
                    'sid' => $sid,
                    'salt' => $salt
                );
                $key = base64_encode(json_encode($token));
                $user->where($where_query)->update(['lasttime' => $time]);

                $data['status'] = ReturnCode::SUCCESS;
                $data['info'] = Tools::errorCode(ReturnCode::SUCCESS);
                $data['token'] = $key;
                $auth = md5(uniqid() . time());
                // 缓存登录信息
                // cache('Login:' . $user->id, $key, config('ONLINE_TIME'));
                cache('Login:' . $user->id, $key);
            } else {
                $data['status'] = ReturnCode::AUTH_ERROR;
                $data['info'] = Tools::errorCode(ReturnCode::AUTH_ERROR);
            }
        }
        return json($data);
    }

    // 退出系统
    public function secede()
    {
        // 从头信息中取出信息，清除登录信息
        $token = Request::header('Token');
        // 解析token验证
        $key = json_decode(base64_decode($token, true),true);
        $uid = json_decode(base64_decode($key['sid']));
        cache('Login:' . $uid, null);
        // 缓存登录信息
        return json(array(
            'status' => ReturnCode::SUCCESS,
            'info'   => Tools::errorCode(ReturnCode::SUCCESS),
        ));
    }
}
