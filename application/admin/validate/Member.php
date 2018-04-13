<?php
namespace app\admin\validate;
use think\Validate;
class Member extends Validate
{
    protected $rule =   [
        'username|客户名称' => 'require|min:2|token',
        'phone|手机号'   => 'length:11|number',
        'weixin|微信号'  => 'between:6,20',
    ];
    protected $message  =   [
        'username.require' => '客户名称必须存在',
        'username.min'     => '客户名称最少2个字符',
        'phone.number'  => '手机号必须是数字',
        'phone.length'  => '手机号长度在11位',
        'weixin.between'=> '微信号在6-20位字符',
    ];
}
