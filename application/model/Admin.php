<?php
namespace app\model;
use think\Model;
use think\Validate;
use app\Util\ReturnCode;
use app\Util\Tools;
class Admin extends Model
{
    //主键
    protected $pk = 'id';
    protected $table='admin';


    protected $rule =   [
        'users|用户名'     => 'min:2',
        'phone|手机号'     => 'length:11|number|/^1[3-8]{1}[0-9]{9}$/',
        'weixin|微信号'    => 'length:6,20|/^[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}+$/',
        'qq1|QQ1'          => 'number|length:6,10',
        'qq2|QQ2'          => 'number|length:6,10',
        'qq3|QQ3'          => 'number|length:6,10',
        'qq4|QQ4'          => 'number|length:6,10',
    ];
    protected $message  =   [
        'users.min'                                 => '用户名最少2个字符',
        'phone.number'                              => '手机号必须是数字',
        'phone.length'                              => '手机号长度在11位',
        'weixin.length'                             => '微信号在6-20位字符',
        'phone./^1[3-8]{1}[0-9]{9}$/'               => '请输入正确的手机号',
        'qq1.number'                                => 'QQ1必须是数字',
        'qq1.length'                                => 'QQ1在6-20位字符',
        'qq2.number'                                => 'QQ2必须是数字',
        'qq2.length'                                => 'QQ2在6-20位字符',
        'qq3.number'                                => 'QQ3必须是数字',
        'qq3.length'                                => 'QQ3在6-20位字符',
        'qq4.number'                                => 'QQ4必须是数字',
        'qq4.length'                                => 'QQ4在6-20位字符',
        'weixin./^[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}+$/'=> '请输入正确的微信号',
    ];
    public function store($data)
    {
        $validate  = Validate::make($this->rule,$this->message);
        $result = $validate->check($data);
        if(!$result) {
            return ['code' => ReturnCode::ERROR,'msg' => $validate->getError()];
        }
        if(isset($data['id'])){
            $preview = $this->where(array('users'=>$data['users']))->find();
            if( $data['pwd'] != $preview['pwd'] && $data['pwd'] != ''){
    	        $data['pwd'] = $data['pwd'];
    	    }else{
    	    	unset($data['pwd']);
    	    }
            $result = $this->update($data);
        }else{
            $result = $this->insertGetId($data);
        }
        if($result){
            return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
        }else {
            return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
        }
    }
}
