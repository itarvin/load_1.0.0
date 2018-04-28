<?php
namespace app\common\model;
use think\Model;
use think\Validate;
use app\util\ReturnCode;
use app\util\Tools;
use app\common\model\Admin;
class Member extends Model
{
    //主键
    protected $pk = 'id';
    protected $table='member';

    protected $rule = [
        'qq|QQ'        => 'number|length:6,11|unique:Member',
        'phone|手机号' => 'length:11|number|/^1[3-8]{1}[0-9]{9}$/|unique:Member',
        // 'weixin|微信号'=> 'length:6,20|/^[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}+$/|unique:Member',
        'weixin|微信号'=> 'length:6,20|unique:Member',
    ];
    protected $msg = [
        'qq.length'                                  => 'QQ号在6-11位',
        'qq.number'                                  => 'QQ号必须是数字',
        'phone.length'                               => '手机号长度在11位',
        'phone.number'                               => '手机号必须是数字',
        'phone./^1[3-8]{1}[0-9]{9}$/'                => '请输入正确的手机号',
        'weixin.length'                              => '微信号在6-20位',
        // 'weixin./^[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}+$/' => '请输入正确的微信号',
    ];

    public function store($data)
    {
        // 先检测当前数据是否存在其他行列
        $id = isset($data['id']) ? $data['id'] : '';
        $result = $this->checkValue($data, $id);
        if($result){
            if($result['uid'] == $data['uid']){
                $info = '已被你登记存在';
                return ['code' => ReturnCode::ERROR, 'msg' => $info];
            }else{
                $info = '已被'.$result['users'].'登记存在';
                return ['code' => ReturnCode::ERROR, 'msg' => $info];
            }
        }
        // 检测修改是否为所有者
        if(isset($data['id'])){
            $exist = $this->where('id','EQ',$data['id'])->find();
            if($exist['uid'] != $data['uid']){
                return ['code' => ReturnCode::OCCUPIED, 'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
            }
        }
        if(empty($data['qq']) && empty($data['phone']) && empty($data['weixin'])){
            return ['code' => ReturnCode::ERROR, 'msg' => 'QQ, 微信，电话不能同时为空！'];
        }
        // 基础数据验证
        $validate  = Validate::make($this->rule,$this->msg);
        $result = $validate->check($data);
        if(!$result) {
            return ['code' => ReturnCode::ERROR,'msg' => $validate->getError()];
        }
        // 数据处理
        if(isset($data['id'])){
            // ------日志处理 ---start
            writelog($data['id'], Tools::logactKey('cus_change'), $data['uid'],$data);
            // -------------------end
            if($this->update($data)){
                return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
            }else {
                return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
            }
        }else{
            if($lastid = $this->insertGetId($data)){
                // ------日志处理 ---start
                writelog($lastid, Tools::logactKey('cus_insert'), $data['uid']);
                // -------------------end
                return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
            }else {
                return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
            }
        }
    }

    // 检测是否QQ，phone，weixin列是否存在当前值
    protected function checkValue($value,$id = '')
    {
        $qq = [];
        $phone = [];
        $weixin = [];
        $qq[] = ['qq|weixin|phone', 'EQ', $value['qq']];
        $phone[] = ['qq|weixin|phone', 'EQ', $value['phone']];
        $weixin[] = ['qq|weixin|phone', 'EQ', $value['weixin']];
        $check = $this->whereOr($qq)->whereOr($weixin)->whereOr($phone)->find();
        if($check){
            if($id != '' && $check['id'] != $id){
                $user = Admin::field('users')->where('id', 'EQ', $check['uid'])->find();
                return $result = array(
                    'user' => $user['users'],
                    'uid' => $check['uid']
                );
            }else {
                return false;
            }
        }else{
            return false;
        }
    }
}
