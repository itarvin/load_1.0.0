<?php
namespace app\index\controller;
use app\admin\model\Administrators;
use think\facade\Request;
use app\util\Tools;
use app\util\ReturnCode;
use think\Validate;
use think\Controller;
class Login extends Controller
{
    public function index()
    {
        $allow_origin = config('TRENDS_ALLOW_ORIGIN');
        // 跨域验证
        if(is_array($allow_origin))
        {
            $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
            if(in_array($origin, $allow_origin)){
                header('Access-Control-Allow-Origin:'.$origin);
            }
        }else if(is_string($allow_origin)){
            header('Access-Control-Allow-Origin:'.$allow_origin);
        }
        header('Access-Control-Allow-Methods:POST,GET');
        header('Access-Control-Allow-Headers:Token,X-Requested-With');
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
                    'salt' => $salt,
                    'deadline' => (time()+3600*12)
                );
                $key = base64_encode(json_encode($token));
                $user->where($where_query)->update(['lasttime' => $time]);

                $data['status'] = ReturnCode::SUCCESS;
                $data['info'] = Tools::errorCode(ReturnCode::SUCCESS);
                $data['token'] = $key;
            } else {
                $data['status'] = ReturnCode::AUTH_ERROR;
                $data['info'] = Tools::errorCode(ReturnCode::AUTH_ERROR);
            }
        }
        return json($data);
    }


    public function secede()
    {

    }
}
