<?php
namespace app\api\controller;
/**
 * 登录处理类
 * @author  itarvin itarvin@163.com
 */
use app\model\Member;
use think\facade\Request;
use app\Util\Tools;
use app\Util\ReturnCode;
use think\Validate;
use think\Controller;
class Index extends Controller
{

    /**
     * 未登录查询
     * @return json
     */
    public function index()
    {
        if(request()->isPost()){
            $member = new Member;
            $result = $member->publicSearch(Request::param('value','','trim'));
            return buildReturn(['status' => $result['code'],'info'=>  $result['msg'],'data' => $result['data']]);
        }else {
            return buildReturn(['status' => ReturnCode::LACKOFPARAM,'info'=>  Tools::errorCode(ReturnCode::LACKOFPARAM)]);
        }
    }
}
