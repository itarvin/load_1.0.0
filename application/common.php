<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use app\admin\model\Log;
// 应用公共文件
function isMobile()
{
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
        return true;
    }
    if (isset ($_SERVER['HTTP_VIA'])){
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    if (isset ($_SERVER['HTTP_USER_AGENT'])){
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
            );
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
            return true;
        }
    }
    if (isset ($_SERVER['HTTP_ACCEPT'])){
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
            return true;
        }
    }
    return false;
}


// 写入日志
function writelog($data,$act,$uid)
{
    $note = [];
    if($act == 10){
        $arr1 = [];
        $arr2 = [];
        $arr3 = [];
        foreach($data as $k => $v){
            $phk = array_key_exists('phone',$v);
            $qqk = array_key_exists('qq',$v);
            $wxk = array_key_exists('weixin',$v);
            if(($phk && $qqk && $wxk) || ($phk && $qqk)){
                $arr1[] = $v['qq'];
                $arr2[] = $v['phone'];
            }else if(($qqk && $wxk) || $qqk){
                $arr1[] = $v['qq'];
            }else if(($phk && $wxk) || $phk){
                $arr2[] = $v['phone'];
            }else if($wxk){
                $arr3[] = $v['phone'];
            }
        }
        $carr1 = count($arr1);
        $carr2 = count($arr2);
        $carr3 = count($arr3);
        if(($arr1 > 0 && $carr2 > 0 && $carr3 > 0) || ($arr1 > 0 && $carr2>0)){
            $note['data'] = ['qq' => $arr1,'phone' => $arr2];
        }else if(($carr2 > 0 && $carr3 > 0) || $carr2 > 0){
            $note['data'] = ['phone' => $arr2];
        }else if(($carr1 > 0 && $carr3 > 0) || $carr1){
            $note['data'] = ['qq' => $arr0];
        }else if($carr3 > 0) {
            $note['data'] = ['wechat' => $arr3];
        }
    }else if($act == 2) {
        $kh = Db::name('member')->field('username,qq')->find($data['khid']);
        $note['data'] = array(
            'username'=> $kh['username'],
            'product'=> $data['product'],
            'consume'=> $data['price'],
            'note'=> $data['note'],
        );
        $da['qq'] = $kh['qq'];
    }else if($act == 3){
        $kh = Db::name('member')->field('username,qq')->find($data);
        $price = Db::name('record')->field('price')->where('khid','EQ',$data)->select();
        $sum = 0;
        foreach($price as $k => $v){
            $sum += $v['price'];
        }
        $note['data'] = array(
            'username'=> $kh['username'],
            'consume'=> $sum,
        );
        $da['qq'] = $kh['qq'];
    }
    $da['act'] = $act;
    $da['newtime'] = date('Y-m-d H:i:s',time());
    $da['uid'] = $uid;
    $da['note'] = json_encode($note);
    $da['ip'] = $_SERVER['REMOTE_ADDR'];
    Db::name('logs')->insert($da);
}



function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
    if(function_exists("mb_substr")){
        if($suffix)
            return mb_substr($str, $start, $length, $charset)."...";
        else
            return mb_substr($str, $start, $length, $charset);
    }elseif(function_exists('iconv_substr')) {
        if($suffix)
            return iconv_substr($str,$start,$length,$charset)."...";
        else
            return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
    $re['gbk']    = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
    $re['big5']   = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}



/**
 * 对象 转 数组
 *
 * @param object $obj 对象
 * @return array
 */
function object_to_array($obj) {
    $obj = (array)$obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array)object_to_array($v);
        }
    }

    return $obj;
}



/**
 * 数组 转 对象
 *
 * @param array $arr 数组
 * @return object
 */
function array_to_object($arr) {
    if (gettype($arr) != 'array') {
        return;
    }
    foreach ($arr as $k => $v) {
        if (gettype($v) == 'array' || getType($v) == 'object') {
            $arr[$k] = (object)array_to_object($v);
        }
    }

    return (object)$arr;
}
