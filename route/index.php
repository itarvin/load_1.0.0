<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// Route::miss('public/miss');
$afterBehavior = [
    '\app\index\behavior\ApiAuth',
];
return [
    '[index/index]' => [
        'verify' => [
            'index/login/verify',
            ['method' => 'get']
        ],
        'login' => [
            'index/login/index',
            ['method' => 'post', 'after_behavior' => $afterBehavior]
        ],
        'logint' => [
            'index/login/logint',
            ['method' => 'get', 'after_behavior' => $afterBehavior]
        ],
        'secede' => [
            'index/login/secede',
            ['method' => 'post', 'after_behavior' => $afterBehavior]
        ],
        'increased' => [
            'index/index/increased',
            ['method' => 'get', 'after_behavior' => $afterBehavior]
        ],
        'geteditinfo' => [
            'index/index/geteditinfo',
            ['method' => 'get', 'after_behavior' => $afterBehavior]
        ],
        'modify' => [
            'index/index/modify',
            ['method' => 'post', 'after_behavior' => $afterBehavior]
        ],
        'list' => [
            'index/index/list',
            ['method' => 'post', 'after_behavior' => $afterBehavior]
        ],
        'getinformation' => [
            'index/index/getinformation',
            ['method' => 'post', 'after_behavior' => $afterBehavior]
        ],
        '__miss__'      => ['index/login/index'],
    ],
];
