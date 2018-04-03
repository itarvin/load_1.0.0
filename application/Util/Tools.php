<?php
/**
 * 工具类
 * @since   2018/03/24 创建
 * @author  iatrvin <iatrvin@163.com>
 */

namespace app\util;
class Tools {
     /**
     * 错误码对比
     * @return string
     * @author iatrivn <iatrivn@163.com>
     */
    static public function errorCode($code)
    {
    	$errorInfo = array(
    		ReturnCode::SUCCESS              => '请求成功',
    		ReturnCode::ERROR                => '请求成功',
    		ReturnCode::NODATA               => '数据不存在',
    		ReturnCode::AUTH_ERROR           => '权限认证失败',
    		ReturnCode::UNKNOWN              => '未知错误',
    		ReturnCode::EXCEPTION            => '系统异常',
    	);
    	return $code ? $errorInfo[$code] : '未知错误';
    }
    static public function fieldMapped($array)
    {
        $field = array(
            'username'  => '客户名称',
            'birthday'  => '生日',
            'age'       => '年龄',
            'qq'        => 'QQ',
            'qq_name'   => 'QQ昵称',
            'phone'     => '电话',
            'weixin'    => '微信号',
            'note'      => '备注',
            'newtime'   => '添加时间',
            'address'   => '地址'
        );
        foreach($array as $k => $v){
            foreach($field as $kv => $v1){
                if($kv == $v){
                    $reField[] = $v1;
                }
            }
        }
        return $reField;
    }


    /**
     * 读取CSV文件
     * @param string $csv_file csv文件路径
     * @param int $lines       读取行数
     * @param int $offset      起始行数
     * @return array|bool
     */
    static public function read_csv_lines($csv_file = '', $lines = 0, $offset = 0)
    {
        if (!$fp = fopen($csv_file, 'r')) {
            return false;
        }
        $i = 0;
        $j = 1;
        while (false !== ($line = fgets($fp))) {
            if ($i++ < $offset) {
                continue;
            }
            break;
        }
        $data = array();
        while (($j++ < $lines) && !feof($fp)) {
            $re = fgetcsv($fp);
            // 转码
            foreach($re as $k => $v){
                $da[$k] = iconv('gbk','utf-8',$v);
            }
            $data[] = $da;
        }
        fclose($fp);
        return $data;
    }


    /**
    * 字符串半角和全角间相互转换
    * @param string $str 待转换的字符串
    * @param int  $type TODBC:转换为半角；TOSBC，转换为全角
    * @return string 返回转换后的字符串
    */
    static public function convertStrType($str, $type) {
        $dbc = array(
          '０' , '１' , '２' , '３' , '４' ,
          '５' , '６' , '７' , '８' , '９' ,
          'Ａ' , 'Ｂ' , 'Ｃ' , 'Ｄ' , 'Ｅ' ,
          'Ｆ' , 'Ｇ' , 'Ｈ' , 'Ｉ' , 'Ｊ' ,
          'Ｋ' , 'Ｌ' , 'Ｍ' , 'Ｎ' , 'Ｏ' ,
          'Ｐ' , 'Ｑ' , 'Ｒ' , 'Ｓ' , 'Ｔ' ,
          'Ｕ' , 'Ｖ' , 'Ｗ' , 'Ｘ' , 'Ｙ' ,
          'Ｚ' , 'ａ' , 'ｂ' , 'ｃ' , 'ｄ' ,
          'ｅ' , 'ｆ' , 'ｇ' , 'ｈ' , 'ｉ' ,
          'ｊ' , 'ｋ' , 'ｌ' , 'ｍ' , 'ｎ' ,
          'ｏ' , 'ｐ' , 'ｑ' , 'ｒ' , 'ｓ' ,
          'ｔ' , 'ｕ' , 'ｖ' , 'ｗ' , 'ｘ' ,
          'ｙ' , 'ｚ' , '－' , '　' , '：' ,
          '．' , '，' , '／' , '％' , '＃' ,
          '！' , '＠' , '＆' , '（' , '）' ,
          '＜' , '＞' , '＂' , '＇' , '？' ,
          '［' , '］' , '｛' , '｝' , '＼' ,
          '｜' , '＋' , '＝' , '＿' , '＾' ,
          '￥' , '￣' , '｀'
      );
      $sbc = array( //半角
          '0', '1', '2', '3', '4',
          '5', '6', '7', '8', '9',
          'A', 'B', 'C', 'D', 'E',
          'F', 'G', 'H', 'I', 'J',
          'K', 'L', 'M', 'N', 'O',
          'P', 'Q', 'R', 'S', 'T',
          'U', 'V', 'W', 'X', 'Y',
          'Z', 'a', 'b', 'c', 'd',
          'e', 'f', 'g', 'h', 'i',
          'j', 'k', 'l', 'm', 'n',
          'o', 'p', 'q', 'r', 's',
          't', 'u', 'v', 'w', 'x',
          'y', 'z', '-', ' ', ':',
          '.', ',', '/', '%', ' #',
          '!', '@', '&', '(', ')',
          '<', '>', '"', '\'','?',
          '[', ']', '{', '}', '\\',
          '|', '+', '=', '_', '^',
          '￥','~', '`'
      );
      if($type == 'TODBC'){
          return str_replace( $sbc, $dbc, $str ); //半角到全角
      }elseif($type == 'TOSBC'){
          return str_replace( $dbc, $sbc, $str ); //全角到半角
      }else{
          return $str;
      }
  }
}
