<?php
namespace app\admin\validate;
use think\Validate;
class Member extends Validate
{
    protected $rule =   [
        'username|客户名称' => 'require|min:2',
        'qq|QQ' => 'require|number|length:6,10',
        'phone|手机号'   => 'length:11|number',
        'weixin|微信号'   => 'length:6,20',
    ];
    protected $message  =   [
        'username.require' => '客户名称必须存在',
        'username.min'     => '客户名称最少2个字符',
        'qq.require'       => 'QQ号必须存在',
        'qq.length'        => 'QQ号在6-10位',
        'qq.number'        => 'QQ号必须是数字',
        'phone.length'     => '手机号长度在11位',
        'phone.number'     => '手机号必须是数字',
        'weixin.length'    => '微信号在6-20位',
    ];
}
