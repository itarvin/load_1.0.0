<?php
namespace app\admin\validate;
use think\Validate;
class Member extends Validate
{
    protected $rule =   [
        'qq|QQ' => 'number|length:6,10',
        'phone|手机号'   => 'length:11|number|/^1[3-8]{1}[0-9]{9}$/',
        'weixin|微信号'   => 'length:6,20',
    ];
    protected $message  =   [
        'qq.length'        => 'QQ号在6-10位',
        'qq.number'        => 'QQ号必须是数字',
        'phone.length'     => '手机号长度在11位',
        'phone.number'     => '手机号必须是数字',
        'phone./^1[3-8]{1}[0-9]{9}$/' => '请输入正确的手机号',
        'weixin.length'    => '微信号在6-20位',
    ];
    // 这里不知是5.1的坑还是我没想搞懂。暂未找到解决方法。
    // protected $scene = [
    //     'edit'  =>  [
    //         'qq.unique'=>'unique:Member,qq^id',
    //         'phone.unique'=>'unique:Member,phone^id',
    //         'weixin.unique'=>'unique:Member,weixin^id'
    //     ],
    //     'add'  =>  [
    //         'qq.unique'=>'unique:Member',
    //         'phone.unique'=>'unique:Member',
    //         'weixin.unique'=>'unique:Member'
    //     ],
    //  ];
}
