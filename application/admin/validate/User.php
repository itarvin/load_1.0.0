<?php
namespace app\admin\validate;
use think\Validate;
class User extends Validate
{
    protected $rule =   [
        'users|用户名' => 'require|min:2|token',
        'phone|手机号'   => 'length:11|number',
        'weixin|微信号'  => 'between:6,20',
    ];
    protected $message  =   [
        'users.require' => '用户名必须存在',
        'users.min'     => '用户名最少2个字符',
        'phone.number'  => '手机号必须是数字',
        'phone.length'  => '手机号长度在11位',
        'weixin.between'=> '微信号在6-20位字符',
    ];
}
