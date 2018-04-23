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
use app\util\Tools;
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
        $note = ',';
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
    }else if($act == 2) {
        $note = [];
        $kh = Db::name('member')->field('username,qq')->where('id',$data['khid'])->find();
        $note = json_encode(array(
            'username'=> $kh['username'],
            'product'=> $data['product'],
            'consume'=> $data['price'],
            'note'=> $data['note'],
        ));
        $da['qq'] = $kh['qq'];
    }else if($act == 3 || $act == 5 || $act == 1){
        $kh = Db::name('member')->field('username,qq,newtime,birthday,weixin,phone')->where('id',$data)->find();
        if($act == 3){
            $price = Db::name('record')->field('price')->where('khid','EQ',$data)->select();
            $sum = 0;
            foreach($price as $k => $v){
                $sum += $v['price'];
            }
            $note = json_encode(array(
                'username'=> $kh['username'],
                'consume'=> $sum,
                'birthday'=> $kh['birthday'],
            ));
        }else if($act == 5){
            $note = json_encode(array(
                'reguid'  => $data,
                'username'=> $kh['username'],
                'newtime'=> $kh['newtime'],
            ));
        }else if($act == 1){
            $note = json_encode(array(
                'username'=> $kh['username'],
                'weixin'=> $kh['weixin'],
                'phone'=> $kh['phone'],
                'newtime'=> $kh['newtime'],
            ));
        }
        $da['qq'] = $kh['qq'];
    }else if($act = 4){
        $info = Db::name('record')->field('price,product,khid')->find($data);
        $kh = Db::name('member')->field('qq,username')->find($info['khid']);
        $note = json_encode(array(
            'username' => $kh['username'],
            'product'=> $info['product'],
            'price'=> $info['price'],
        ));
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

    // 对base64二次加密处理
    function encryption($userid,$agent,$salt)
    {
        // 密码薄
        $passwordBook = Tools::makeRandom();
        // 加密UID
        $uid = base64_encode(json_encode($userid));
        $tokens = array(
            'agent' => $agent,
            'salt' => $salt
        );
        // 转为base64
        $key = base64_encode(json_encode($tokens));
        // 计算秘钥长度和用户id长度
        $secretLen = strlen($key);
        $uLen = strlen($uid);
        $random = rand(0,25);
        // 生成随机数
        $start = $passwordBook[$random];
        $end = $passwordBook[$uLen];
        // 分隔字符串
        $tokenStart = mb_substr($key, 0, ($secretLen/2), 'utf-8');
        $uidStart = mb_substr($tokenStart, 0, $random, 'utf-8');
        $uidEnd = mb_substr($tokenStart, $random, strlen($tokenStart), 'utf-8');
        // 生成md5一个介质
        $medium = md5('itarvin'.time());
        // 结束后半部
        $tokenEnd = mb_substr($key,($secretLen/2),$secretLen, 'utf-8');
        // 拼装加密新字符串
        $token = $start.$uidStart.$uid.$uidEnd.$medium.$tokenEnd.$end;
        return $token;
    }

    // 对base64二次加密数据进行析出
    function analysisCode($token)
    {
        // 析出当前数据对比验证
        $passwordBook = Tools::makeRandom();
        // 分离关键字母
        $begin = substr($token,0,1);
        $finish = substr($token,-1);
        $middles = substr($token,1,-1);
        // 定位出UID位置以及单位长度
        $beginKey = array_search($begin,$passwordBook);
        $finishKey = array_search($finish,$passwordBook);
        // 析出用户id
        $uidkey = substr($middles,$beginKey,$finishKey);
        // 合并剩下的字符串
        $all = substr($middles,0,$beginKey).substr($middles,($beginKey+$finishKey));
        // 计算长度
        $allLen = strlen($all);
        // 取出32位介质分离出tokeb前半部和后半部
        $reTokenStart = mb_substr($all, 0, (($allLen-32)/2), 'utf-8');
        $reTokenEnd = mb_substr($all, (($allLen-32)/2) + 32);
        // 合并数据返回
        $result = $reTokenStart.$reTokenEnd;
        return array(
            'uid'   => $uidkey,
            'token' => $result
        );
    }
