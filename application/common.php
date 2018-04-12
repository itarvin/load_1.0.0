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

    if($act == 10){
        $note = '';
        foreach($data as $k => $v){
            $phk = array_key_exists('phone',$v);
            $qqk = array_key_exists('qq',$v);
            $wxk = array_key_exists('weixin',$v);
            if(($phk && $qqk && $wxk) || ($phk && $qqk)){
                $note .= $v['qq'].',';
                $note .= $v['phone'].',';
            }else if(($qqk && $wxk) || $qqk){
                $note .= $v['qq'].',';
            }else if(($phk && $wxk) || $phk){
                $note .= $v['phone'].',';
            }else if($wxk){
                $note .= $v['phone'].',';
            }
        }
        // $note = $arr
        // $carr1 = count($arr1);
        // $carr2 = count($arr2);
        // $carr3 = count($arr3);
        // if(($arr1 > 0 && $carr2 > 0 && $carr3 > 0) || ($arr1 > 0 && $carr2>0)){
        //     $note = ['qq' => $arr1,'phone' => $arr2];
        // }else if(($carr2 > 0 && $carr3 > 0) || $carr2 > 0){
        //     $note = ['phone' => $arr2];
        // }else if(($carr1 > 0 && $carr3 > 0) || $carr1){
        //     $note = ['qq' => $arr0];
        // }else if($carr3 > 0) {
        //     $note = ['wechat' => $arr3];
        // }
    }else if($act == 2) {
        $note = [];
        $kh = Db::name('member')->field('username,qq')->find($data['khid']);
        $note = array(
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
        $note = array(
            'username'=> $kh['username'],
            'consume'=> $sum,
        );
        $da['qq'] = $kh['qq'];
    }else if($act = 4){
        $info = Db::name('record')->field('price,product,khid')->find($data);
        $kh = Db::name('member')->field('qq,username')->find($info['khid']);
        $note = array(
            'username' => $kh['username'],
            'product'=> $info['product'],
            'price'=> $info['price'],
        );
        $da['qq'] = $kh['qq'];
    }
    $da['act'] = $act;
    $da['newtime'] = date('Y-m-d H:i:s',time());
    $da['uid'] = $uid;
    $da['note'] = $note;
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



    /**
     * 设置关键词高亮的字符串处理函数
     * @param [string] $str      [要高亮的字符串]
     * @param array  $word_arr [关键词]
     */
    function setKeyWords($str,$word_arr=array()){
        // 设置多字节字符内部编码为utf8
        mb_internal_encoding("UTF-8");
        // 创建一个跟字符串长度一致的数组，用0填充
        $map = array_fill(0,mb_strlen($str),0);
        // 遍历关键词数组，将关键词对应的map数组的位置上的数字置为1
        foreach ($word_arr as $value) {
            $pos=-1;
            $pos_count=0;
            $pos_arr=array();
            // 如果找到了这个关键词，就将这个词的位置存入位置数组中（来支持多次出现此关键词的情况）
            while(($pos=mb_strpos($str,$value,$pos+1))!==false && $pos_count<5){
                $pos_arr[]=$pos;
                $pos_count++;
            }
            // 遍历数组，将对应位置置1
            foreach ($pos_arr as $pos_val) {
                if($pos_val!==false){
                    $fill=array_fill($pos_val,mb_strlen($value),1);
                    $map = array_replace($map,$fill);
                }
            }
            $pos=null;
        }
        // 遍历map数组，加入高亮代码
        $flag=0;
        $position=-1;
        $result="";  // 结果数组
        foreach ($map as $key => $value) {
            if($value==1){
                // 如果第一次出现1,则加上html标签头
                if($flag==0) $result.="<span class=\"keyword\">";
                $flag=1;
            }else{
                // 如果已经到了一个0,但上一个还是1时，加入html标签尾
                if($flag==1){
                    $position=$key-1;
                    $flag=0;
                    $result.="</span>";
                }
            }
            // 将该位置的字符加入结果字符串中
            $result.=mb_substr($str,$key,1);
        }
        return $result;
    }
