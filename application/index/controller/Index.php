<?php
namespace app\index\controller;
use app\admin\model\Consumer;
use app\util\Tools;
use app\util\ReturnCode;
use think\facade\Request;
use think\Validate;

class Index extends Base
{
    public function index()
    {
        return json($data = [
            'author'    => 'itarvin',
            'ToYou'      => "I'm glad to meet you（终于等到你！）"
        ]);
    }


    // API接口添加数据
    public function increased()
    {
        $member = new Consumer;
        $data = [];
        if($this->AuthPermission == '200'){
            $input = Request::param();
            // 数据验证
            $result = $this->validate($input,'app\admin\validate\Member');
            // 添加时做唯一索引处理
            $rule = [
                'qq|QQ号' => 'unique:member',
                'phone|电话号' => 'unique:member',
                'weixin|微信号' => 'unique:member',
            ];
            $validate = new Validate($rule);
            $unresult = $validate->check($input);

            if($result  !== true){
                $data['status'] = ReturnCode::ERROR;
                $data['info'] = $result;
            }else if(!$unresult){
                $data['status'] = ReturnCode::ERROR;
                $data['info'] = $validate->getError();
            }else{
                $input['uid'] = $this->uid;
                $input['newtime'] = date('Y-m-d H:i:s',time());
                $lastid = $member->insertGetId($input);
                // ------日志处理 ---start
                writelog($lastid,Tools::logactKey('cus_insert'),$this->uid);
                // -------------------end
                if($lastid){
                    $data['status'] = ReturnCode::SUCCESS;
                    $data['info'] = Tools::errorCode(ReturnCode::SUCCESS);
                }else {
                    $data['status'] = ReturnCode::ERROR;
                    $data['info'] = Tools::errorCode(ReturnCode::ERROR);
                }
            }
        }else if($this->AuthPermission == '400') {

            $data['ToYou'] = "I'm glad to meet you（终于等到你！）";
        }else if($this->AuthPermission == '300') {

            $data['status'] = ReturnCode::ACCOUNTEXPIRED;
            $data['info'] = Tools::errorCode(ReturnCode::ACCOUNTEXPIRED);
        }

        return json($data);
    }



    // 修改数据
    public function  modify()
    {
        $member = new Consumer;
        $data = [];

        if($this->AuthPermission == '200'){

            $input = Request::param();
            $key = cache($input['key']);
            $preview = $member->field('uid')->find($key);

            if($preview['uid'] != $this->uid){
                $data['status'] = ReturnCode::OCCUPIED;
                $data['info'] = Tools::errorCode(ReturnCode::OCCUPIED);
            }else {
                unset($input['key']);
                $input['id'] = $key;
                // 数据验证
                $result = $this->validate($input,'app\admin\validate\Member');
                if(true !== $result){
                    $data['status'] = ReturnCode::ERROR;
                    $data['info'] = $result;
                }else{
                    $input['uid'] = $this->uid;

                    // ------日志处理 ---start
                    writelog($input['id'],Tools::logactKey('cus_change'),$this->uid);
                    // -------------------end

                    if($member->update($input)){
                        $data['status'] = ReturnCode::SUCCESS;
                        $data['info'] = Tools::errorCode(ReturnCode::SUCCESS);
                    }else {
                        $data['status'] = ReturnCode::ERROR;
                        $data['info'] = Tools::errorCode(ReturnCode::ERROR);
                    }
                }
            }
        }else if($this->AuthPermission == '400') {

            $data['ToYou'] = "I'm glad to meet you（终于等到你！）";
        }else if($this->AuthPermission == '300') {

            $data['status'] = ReturnCode::ACCOUNTEXPIRED;
            $data['info'] = Tools::errorCode(ReturnCode::ACCOUNTEXPIRED);
        }
        return json($data);
    }


    // 获取详情信息
    public function getinformation()
    {
        $member = new Consumer;
        $input = Request::param();
        $data = [];

        if($this->AuthPermission == '200'){

            $where = [];
            if($input['type'] == 'all' || $input['type'] == ''){
                $where[] = ['phone|weixin|qq','eq',$input['key']];
            }else if($input['type'] == 'qq' || $input['type'] == 'phone' || $input['type'] == 'weixin'){
                $where[] = [$input['type'],'eq',$input['key']];
            }
            $list = $member->where($where)->find();

            if(!empty($list) && $list['uid'] == $this->uid){
                // 加密键返回
                $list['key'] = hash('sha512',$list['id'].'itarvin');
                // 缓存当前键
                cache($list['key'],$list['id']);
                $data['status'] = ReturnCode::SUCCESS;
                $data['info'] = Tools::errorCode(ReturnCode::SUCCESS);
                $data['data'] = $list;
            }else if($list['uid'] != $this->uid) {

                $data['status'] = ReturnCode::OCCUPIED;
                $data['info'] = Tools::errorCode(ReturnCode::OCCUPIED);
            }else if(empty($list)) {

                $data['status'] = ReturnCode::NODATA;
                $data['info'] = Tools::errorCode(ReturnCode::NODATA);
            }

        }else if($this->AuthPermission == '400') {

            $data['ToYou'] = "I'm glad to meet you（终于等到你！）";
        }else if($this->AuthPermission == '300') {

            $data['status'] = ReturnCode::ACCOUNTEXPIRED;
            $data['info'] = Tools::errorCode(ReturnCode::ACCOUNTEXPIRED);
        }

        return json($data);
    }


    // 根据键key 获取其对应信息
    public function geteditinfo()
    {
        if($this->AuthPermission == '200'){
            $member = new Consumer;
            $key = Request::param('uid','','strip_tags','trim');
            $uid = cache($key);
            $list = $member->find($uid);
            $list['key'] = $key;
            if($list['uid'] == $this->uid){
                $data['status'] = ReturnCode::SUCCESS;
                $data['info'] = Tools::errorCode(ReturnCode::SUCCESS);
                $data['data'] = $list;
            }else {
                $data['status'] = ReturnCode::OCCUPIED;
                $data['info'] = Tools::errorCode(ReturnCode::OCCUPIED);
            }
        }else if($this->AuthPermission == '400') {

            $data['ToYou'] = "I'm glad to meet you（终于等到你！）";
        }else if($this->AuthPermission == '300') {

            $data['status'] = ReturnCode::ACCOUNTEXPIRED;
            $data['info'] = Tools::errorCode(ReturnCode::ACCOUNTEXPIRED);
        }
        return json($data);
    }
}
