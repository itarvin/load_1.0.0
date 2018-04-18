<?php
namespace app\index\controller;
use app\admin\model\Consumer;
use app\util\Tools;
use app\util\ReturnCode;
class Index extends Base
{
    public function index()
    {
        return json($data = [
            'author'    => 'itarvin',
            'ToYou'      => "I'm glad to meet you（终于等到你！）"
        ]);
    }
    public function increased()
    {
        $member = new Consumer;
        $data = [];
        if($this->AuthPermission == '200'){
            $input = input('post.');
            // 数据验证
            $result = $this->validate($input,'app\admin\validate\Member');
            if(!$result){
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
                }
            }
        }else {
            $data['ToYou'] = "I'm glad to meet you（终于等到你！）";
        }
        return json($data);
    }
    // 修改数据
    // public function increased()
    // {
    //     $member = new Consumer;
    //     $data = [];
    //     if($this->AuthPermission == '200'){
    //         $input = input('post.');
    //         // 数据验证
    //         $result = $this->validate($input,'app\admin\validate\Member');
    //         if(!$result){
    //             $data['status'] = ReturnCode::ERROR;
    //             $data['info'] = $validate->getError();
    //         }else{
    //             $input['uid'] = $this->uid;
    //             $re = $member->upadte($input);
    //             if($re){
    //                 $data['status'] = ReturnCode::SUCCESS;
    //                 $data['info'] = Tools::errorCode(ReturnCode::SUCCESS);
    //             }
    //         }
    //     }else {
    //         $data['ToYou'] = "I'm glad to meet you（终于等到你！）";
    //     }
    //     return json($data);
    // }


    // 获取详情信息
    public function getinformation()
    {
        $member = new Consumer;
        $input = input('post.');
        $data = [];
        if($this->AuthPermission == '200'){
            $where = [];
            if($input['type'] == 'all' || $input['type'] == ''){
                $where[] = ['phone|weixin|qq','eq',$input['key']];
            }else if($input['type'] == 'qq' || $input['type'] == 'phone' || $input['type'] == 'weixin'){
                $where[] = [$input['type'],'eq',$input['key']];
            }
            $list = $member->where($where)->order('id desc')->select();
            if(count($list) > 0){
                $data['status'] = ReturnCode::SUCCESS;
                $data['info'] = Tools::errorCode(ReturnCode::SUCCESS);
                $data['data'] = $list;
            }else {
                $data['status'] = ReturnCode::NODATA;
                $data['info'] = Tools::errorCode(ReturnCode::NODATA);
            }
        }else if($this->AuthPermission == '400') {
            $data['ToYou'] = "I'm glad to meet you（终于等到你！）";
        }
        return json($data);
    }
}
