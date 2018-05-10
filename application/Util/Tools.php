<?php
/**
 * 工具类
 * @since   2018/03/24 创建
 * @author  iatrvin <iatrvin@163.com>
 */

namespace app\util;
use app\model\Logact;
class Tools {

    /**
    * 上传
    * @return string
    * @author iatrivn <iatrivn@163.com>
    */
    static public function upload($field = '', $upload_path, $filename)
    {
        // 上传配置
        $config = [
            'size' => 1024*1024*4,
            'ext'  => 'jpg,gif,png,bmp,jpeg'
        ];

        $file = request()->file($field);
        // 清除存在的文件
        $existFile = ['jpg','gif','png','bmp','jpeg'];

        if($file != null){
            foreach($existFile as $k => $v){
                $tmpFileName = $filename . $v;
                unlink(config('upload_path') . $upload_path . '/'. $tmpFileName);
            }

            $info = $file->validate($config)->move(config('upload_path') . $upload_path,$filename);

            if(!$info){
                return json(['code' = '0','msg' => $file->getError()]);
            }else {
                return json(['code' = '1','msg' => $upload_path.'/'.$file->getSaveName()]);
            }
        }
    }

     /**
     * 错误码对比
     * @return string
     * @author iatrivn <iatrivn@163.com>
     */
    static public function errorCode($code)
    {
    	$errorInfo = array(
    		ReturnCode::SUCCESS              => '请求成功',
    		ReturnCode::ERROR                => '请求失败',
    		ReturnCode::NODATA               => '数据不存在',
    		ReturnCode::AUTH_ERROR           => '权限认证失败',
    		ReturnCode::UNKNOWN              => '未知错误',
    		ReturnCode::EXCEPTION            => '系统异常',
            ReturnCode::VERIFICATIONFAILURE  => '数据验证失败',
            ReturnCode::ACCOUNTEXPIRED       => '账户已过期',
            ReturnCode::OCCUPIED             => '非您的客户！',
            ReturnCode::LACKOFPARAM          => '缺少合法参数',
            ReturnCode::LOCKACCOUNT          => '账户异常，已被冻结！请联系管理员释放',


    	);
    	return $code ? $errorInfo[$code] : '未知错误';
    }

    static public function fieldMapped($string)
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
        $string = str_replace("，",",",$string);
        $array = explode(",",$string);
        $reField = [];
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
        $data = [];
        while (($j++ <= $lines) && !feof($fp)) {
            $re = fgetcsv($fp);
            for ($i = 0; $i < count($re); $i++) {
                $da[$i] = iconv('gbk','utf-8',$re[$i]);
            }
            $data[] = $da;
        }
        fclose($fp);
        return $data;
    }


    // 行为事件转键值
    static public function logactKey($string)
    {
        $list = cache('Logact:list');
        if(!$list){
            $list = Logact::field('id,act')->select();
            cache('Logact:list',$list);
        }
        $data = [];
        foreach ($list as $k => $v) {
            $data[$v['id']] = $v['act'];
        }
        $key = array_search($string,$data);
        return $key;
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


  // 关键字替换原有标识
    static public function keywordReplace($string)
    {
        $array = array(
            'username' => '名字',
            'consume'  => '消费',
            'product'  => '产品',
            'note'     => '备注',
            'phone'    => '手机号',
            'qq'       => 'QQ',
            'wechat'   => '微信号',
            'reguid'   => '原ID',
            'birthday' => '生日',
            'newtime'  => '添加时间',
            'id'       => '新增id',
            'sex'      => '性别',
            'uid'      => '销售id',
            'weixin'   => '微信',
            'address'  => '地址',
        );
        $keyword = array_search($string,array_flip($array));
        return $keyword;
    }

    // 密码薄
    static public function makeRandom()
    {
        $array = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        return $array;
    }
}
