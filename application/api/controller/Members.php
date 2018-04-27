<?php
namespace app\api\controller;
use app\common\model\Member;
use app\util\Tools;
use app\util\ReturnCode;
use think\facade\Request;
use think\Validate;
use think\facade\Cookie;
use \Think\Db;
class Members extends Base
{
    /**
     * 添加客户信息接口
     * @return json
     */
    public function add()
    {
        if(request()->isPost()){
            $member = new Member;
            $data = Request::param();
            $data['uid'] = $this->uid;
            $data['newtime'] = date('Y-m-d H:i:s', time());
            $result = $member->store($data);
            return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
        }
    }


    /**
     * 修改客户信息接口
     * @return json
     */
    public function edit()
    {
        if(request()->isPost()){
            $member = new Member;
            // 接收所有参数
            $data = Request::param();
            $data['uid'] = $this->uid;
            $result = $member->store($data);
            return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
        }
    }

    /**
     * 查询信息接口
     * @return json
     */
    public function getinfo()
    {
        if(request()->isPost()){
            $member = new Member;
            $input = Request::param();
            // 验证
            if(!empty($input['key']) && !empty($input['type'])){
                // 拼接查询条件
                $where = [];
                if($input['type'] == 'all' || $input['type'] == ''){
                    $where[] = ['phone|weixin|qq', 'eq', $input['key']];
                }else if($input['type'] == 'qq' || $input['type'] == 'phone' || $input['type'] == 'weixin'){
                    $where[] = [$input['type'], 'eq', $input['key']];
                }
                // 查询对象
                $list = $member->where($where)->find();
                // 对象存在且为操作人的客户，返回数据
                if(!empty($list) && $list['uid'] == $this->uid){
                    return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS), 'data' => $list]);
                }else if($list['uid'] != $this->uid) {
                    return buildReturn(['status' => ReturnCode::OCCUPIED,'info'=> Tools::errorCode(ReturnCode::OCCUPIED)]);
                }else if(empty($list)) {
                    return buildReturn(['status' => ReturnCode::NODATA,'info'=> Tools::errorCode(ReturnCode::NODATA)]);
                }
            }else {
                return buildReturn(['status' => ReturnCode::LACKOFPARAM,'info'=> Tools::errorCode(ReturnCode::LACKOFPARAM)]);
            }
        }
    }

    /**
     * 键key获取查询信息接口
     * @param uid
     * @return json
     */
    public function info()
    {
        if(request()->isPost()){
            $member = new Member;
            $uid = Request::param('uid', '', 'strip_tags', 'trim');
            $list = $member->find($uid);
            // 验证客户的操作者
            if($list['uid'] == $this->uid){
                return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS), 'data' => $list]);
            }else {
                return buildReturn(['status' => ReturnCode::OCCUPIED,'info'=> Tools::errorCode(ReturnCode::OCCUPIED)]);
            }
        }
    }

    /**
     * 分页数据接口
     * @param page(post)
     * @return json
     */
    public function index()
    {
        if(request()->isPost()){
            // 传值默认取
            $page = Request::param('page', '1', 'strip_tags', 'trim');
            $member = new Member;
            $lists = $member->where(array('uid' => $this->uid))->order('id desc')->page($page, 15)->select();
            $list['data'] = $lists;
            $list['page'] = $page;
            return buildReturn(['status' => ReturnCode::SUCCESS,'info'=>  Tools::errorCode(ReturnCode::SUCCESS),'data' => $list]);
        }
    }
}
