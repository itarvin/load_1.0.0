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
use app\model\Log;
use app\Util\Tools;
/**
 * 统一接口输出值
 * @param $res
 * @return json
 */
function buildReturn($res) {
    $result = [
        'status' => isset($res['status']) ? $res['status'] : '',
        'info'   => isset($res['info']) ? $res['info'] : '',
        'data'   => isset($res['data']) ? $res['data'] : []
    ];
    return json($result);
}

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
function writelog($data,$act,$uid,$edits = '')
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
        $kh = Db::name('member')->where('id',$data['khid'])->find();
        $note = json_encode(array(
            'username'=> $kh['username'],
            'product'=> $data['product'],
            'consume'=> $data['price'],
            'note'=> $data['note'],
        ));
        $da['qq'] = $kh['qq'];
        $da['weixin'] = $kh['weixin'];
        $da['phone'] = $kh['phone'];
    }else if($act == 3 || $act == 5 || $act == 1){
        $kh = Db::name('member')->where('id',$data)->find();
        if($act == 3){
            $price = Db::name('record')->field('price')->where('khid','EQ',$data)->select();
            $sum = 0;
            foreach($price as $k => $v){
                $sum += $v['price'];
            }
            $note = json_encode([
                'username'=> $kh['username'],
                'consume'=> $sum,
                'birthday'=> $kh['birthday'],
            ]);
        }else if($act == 5){
            $jsons = [];
            foreach ($kh as $key => $output) {
                foreach ($edits as $key2 => $input) {
                    if($key == $key2){
                        if($output != $input && !empty($input)){
                            $jsons[$key] = $input;
                        }
                    }
                }
            }
            $note = json_encode($jsons);
        }else if($act == 1){
            $jsons = [];
            foreach ($kh as $key => $value) {
                if(!empty($value)){
                    $jsons[$key] = $value;
                }
            }
            $note = json_encode($jsons);
        }
        if ($note != '[]') {
            $da['qq'] = $kh['qq'];
            $da['weixin'] = $kh['weixin'];
            $da['phone'] = $kh['phone'];
        }
    }else if($act = 4){
        $info = Db::name('record')->field('price,product,khid')->find($data);
        $kh = Db::name('member')->field('qq,username,weixin,phone')->find($info['khid']);
        $note = json_encode([
            'username' => $kh['username'],
            'product'=> $info['product'],
            'price'=> $info['price'],
        ]);
        $da['qq'] = $kh['qq'];
        $da['weixin'] = $kh['weixin'];
        $da['phone'] = $kh['phone'];
    }
    if($note != "[]"){
        $da['act'] = $act;
        $da['newtime'] = date('Y-m-d H:i:s',time());
        $da['uid'] = $uid;
        $da['note'] = $note;
        $da['ip'] = $_SERVER['REMOTE_ADDR'];
        Db::name('logs')->insert($da);
    }
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
