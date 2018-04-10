<?php
// 系统行为定义
return [
	// 应用初始化
    'app_init'     => [],
    // 应用开始
    'app_begin'    => [
		'app\\admin\\behavior\\AdminLog'
	],
    // 模块初始化
    'module_init'  => [],
    // 操作开始执行
    'action_begin' => [],
    // 视图内容过滤
    'view_filter'  => [],
    // 日志写入
    'log_write'    => [],
    // 应用结束
    'app_end'      => [],
];
