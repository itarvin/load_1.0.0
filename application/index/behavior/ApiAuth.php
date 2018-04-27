<?php
/**
 * 处理Api接入认证
 * @since   2018-04-26
 * @author  itarvin <itarvin@163.com>
 */

namespace app\api\behavior;


// use app\model\AdminList;
// use app\util\ApiLog;
// use app\util\ReturnCode;
// use think\Cache;
// use think\Request;

class ApiAuth {

    /**
     * @var Request
     */
    private $request;
    private $apiInfo;

    /**
     * 默认行为函数
     * @author zhaoxiang <zhaoxiang051405@gmail.com>
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function run() {
        // 获取配置允许域名
        $allow_origin = config('TRENDS_ALLOW_ORIGIN');
        $origins = isset($_SERVER['HTTP_ORIGIN']);
        // 跨域验证
        if(is_array($allow_origin))
        {
            // 获取请求来源域
            $origin = $origins ? $_SERVER['HTTP_ORIGIN'] : '';
            if(in_array($origin, $allow_origin)){
                header('Access-Control-Allow-Origin:'.$origin);
            }
        }else if(is_string($allow_origin)){
            header('Access-Control-Allow-Origin:'.$allow_origin);
        }
        // 请求头
        header('Access-Control-Allow-Methods:POST,GET');
        header('Access-Control-Allow-Headers:Token,Cookie,X-Requested-With,User-Agent');
        if($allow_origin){
            echo '123';die;
        }
        // if(is_array($allow_origin)){
        //     return json(['code' => '1651651']);
        // }
    }
}
