<?php
namespace app\common\model;
use think\Model;
use think\Validate;
use app\util\ReturnCode;
use app\util\Tools;
class Member extends Model
{
    //主键
    protected $pk = 'id';
    protected $table='member';

    protected $rule = [
        'qq|QQ'          => 'number|length:6,10|unique:Member',
        'phone|手机号'    => 'length:11|number|/^1[3-8]{1}[0-9]{9}$/|unique:Member',
        'weixin|微信号'   => 'length:6,20|/^[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}+$/|unique:Member',
    ];
    protected $msg = [
        'qq.length'      => 'QQ号在6-10位',
        'qq.number'      => 'QQ号必须是数字',
        'phone.length'   => '手机号长度在11位',
        'phone.number'   => '手机号必须是数字',
        'phone./^1[3-8]{1}[0-9]{9}$/'   => '请输入正确的手机号',
        'weixin.length'                 => '微信号在6-20位',
        'weixin./^[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}+$/'=> '请输入正确的微信号',
    ];
    
    public function store($data)
    {
        if(isset($data['id'])){
            $exist = $this->where('id','EQ',$data['id'])->find();
            if($exist['uid'] != $data['uid']){
                return ['code' => ReturnCode::OCCUPIED, 'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
            }
        }
        if(empty($data['qq']) && empty($data['phone']) && empty($data['weixin'])){
            return ['code' => ReturnCode::ERROR, 'msg' => 'QQ, 微信，电话不能同时为空！'];
        }
        $validate  = Validate::make($this->rule,$this->msg);
        $result = $validate->check($data);
        if(!$result) {
            return ['code' => ReturnCode::ERROR,'msg' => $validate->getError()];
        }
        if(isset($data['id'])){
            if($this->update($data)){
                // ------日志处理 ---start
                writelog($data['id'], Tools::logactKey('cus_change'), $data['uid'],$data);
                // -------------------end
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
}
