<?php
namespace app\api\controller;
/**
 * 接口客户处理类
 * @author  itarvin itarvin@163.com
 */
use app\model\Member;
use app\Util\Tools;
use app\Util\ReturnCode;
use think\facade\Request;
use think\Validate;
use think\facade\Cookie;
use \Think\Db;
class Members extends Base
{

    /**
     * 分页数据接口
     * @param page(post)
     * @return json
     */
    public function index()
    {
        if($this->AuthPermission == '200'){
            if( request()->isPost()){

                $member = new Member;

                $list = $member->search(Request::param(),$this->uid);

                return buildReturn(['status' => ReturnCode::SUCCESS,'info'=>  Tools::errorCode(ReturnCode::SUCCESS),'data' => $list]);
            }else {

                return buildReturn(['status' => ReturnCode::LACKOFPARAM,'info'=>  Tools::errorCode(ReturnCode::LACKOFPARAM)]);
            }
        }else {
            
            return $this->returnRes($this->AuthPermission, 'true');
        }
    }

    /**
     * 添加客户信息接口
     * @return json
     */
    public function add()
    {
        if($this->AuthPermission == '200'){

            if( request()->isPost()){

                $member = new Member;
                $data = Request::param();

                $data['uid'] = $this->uid;

                $data['newtime'] = date('Y-m-d H:i:s', time());

                $result = $member->store($data);

                return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
            }else {

                return buildReturn(['status' => ReturnCode::LACKOFPARAM,'info'=>  Tools::errorCode(ReturnCode::LACKOFPARAM)]);
            }
        }else {

            return $this->returnRes($this->AuthPermission, 'true');
        }
    }


    /**
     * 修改客户信息接口
     * @return json
     */
    public function edit()
    {
        if($this->AuthPermission == '200'){
            if( request()->isPost()){

                $member = new Member;
                // 接收所有参数
                $data = Request::param();

                $data['uid'] = $this->uid;
                $result = $member->store($data);

                return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
            }else {

                return buildReturn(['status' => ReturnCode::LACKOFPARAM,'info'=>  Tools::errorCode(ReturnCode::LACKOFPARAM)]);
            }
        }else {

            return $this->returnRes($this->AuthPermission, 'true');
        }
    }

    /**
     * 键key获取查询信息接口
     * @param uid
     * @return json
     */
    public function info()
    {
        if($this->AuthPermission == '200'){
            if( request()->isPost()){

                $member = new Member;
                $uid = Request::param('uid', '', 'strip_tags', 'trim');

                $list = $member->field('id,username,sex,calendar,birthday,qq,phone,weixin,note,newtime,address,uid')->find($uid);

                // 验证客户的操作者
                if($list['uid'] == $this->uid){

                    unset($list['uid']);

                    return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS), 'data' => $list]);
                }else {

                    return buildReturn(['status' => ReturnCode::OCCUPIED,'info'=> Tools::errorCode(ReturnCode::OCCUPIED)]);
                }
            }else {

                return buildReturn(['status' => ReturnCode::LACKOFPARAM,'info'=>  Tools::errorCode(ReturnCode::LACKOFPARAM)]);
            }
        }else {

            return $this->returnRes($this->AuthPermission, 'true');
        }
    }


    /**
     * 软删除当前数据，不记录日志
     * @param kid，uid
     * @return json
     */
    public function delete()
    {
        if($this->AuthPermission == '200'){
            if( request()->isPost()){

                $member = new Member;

                $kid = Request::param('kid', '', 'strip_tags', 'trim');
                $result = $member->softDelete($kid, $this->uid);

                return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
            }else {

                return buildReturn(['status' => ReturnCode::LACKOFPARAM,'info'=>  Tools::errorCode(ReturnCode::LACKOFPARAM)]);
            }
        }else {

            return $this->returnRes($this->AuthPermission, 'true');
        }
    }
}
