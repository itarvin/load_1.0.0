<?php
namespace app\model;
use think\Model;
use think\Validate;
use app\Util\ReturnCode;
use app\Util\Tools;
use app\model\Admin;
use think\facade\Cache;
class Member extends Model
{
    //主键
    protected $pk = 'id';
    protected $table='member';

    protected $rule = [
        'qq|QQ'        => 'number|length:6,11',
        'phone|手机号' => 'length:11|number|/^1[3-8]{1}[0-9]{9}$/',
        // 'weixin|微信号'=> 'length:6,20|/^[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}+$/',
        'weixin|微信号'=> 'length:6,20',
    ];
    protected $msg = [
        'qq.length'                                  => 'QQ号在6-11位',
        'qq.number'                                  => 'QQ号必须是数字',
        'phone.length'                               => '手机号长度在11位',
        'phone.number'                               => '手机号必须是数字',
        'phone./^1[3-8]{1}[0-9]{9}$/'                => '请输入正确的手机号',
        'weixin.length'                              => '微信号在6-20位',
        // 'weixin./^[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}+$/' => '请输入正确的微信号',
    ];

    /**
     * 应用场景：新增，修改数据时的数据验证与处理，日志添加
     * @param string $data    所有数据
     * @return array
     */
    public function store($data)
    {
        // 先检测当前数据是否存在其他行列
        $id = isset($data['id']) ? $data['id'] : '';

        $result = $this->checkValue($data, $id);

        if($result){

            if($result['uid'] == $data['uid']){

                $info = '已被你登记存在';
                return ['code' => ReturnCode::ERROR, 'msg' => $info];
            }else{

                $info = '已被'.$result['user'].'登记存在';
                return ['code' => ReturnCode::ERROR, 'msg' => $info];
            }
        }
        // 检测修改是否为所有者
        if(isset($data['id'])){

            $exist = $this->where('id','EQ',$data['id'])->find();

            if($exist['uid'] != $data['uid']){

                return ['code' => ReturnCode::OCCUPIED, 'msg' => Tools::errorCode(ReturnCode::OCCUPIED)];
            }
        }
        if(empty($data['qq']) && empty($data['phone']) && empty($data['weixin'])){

            return ['code' => ReturnCode::ERROR, 'msg' => 'QQ, 微信，电话不能同时为空！'];
        }
        // 基础数据验证
        $validate  = Validate::make($this->rule,$this->msg);
        $result = $validate->check($data);

        if(!$result) {

            return ['code' => ReturnCode::ERROR,'msg' => $validate->getError()];
        }
        // 数据处理
        if(isset($data['id'])){

            // ------日志处理 ---start
            writelog($data['id'], Tools::logactKey('cus_change'), $data['uid'],$data);
            // -------------------end
            if($this->update($data)){

                return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
            }else {

                return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
            }
        }else{
            if($lastid = $this->insertGetId($data)){

                // ------日志处理 ---start
                writelog($lastid, Tools::logactKey('cus_insert'), $data['uid']);
                // -------------------end

                return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
            }else {

                return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
            }
        }
    }

    /**
     * 应用场景：检测是否QQ，phone，weixin其余列是否存在当前值
     * @param string $value    所有数据
     * @param int $id   更新数据时的id
     * @return array
     */
    protected function checkValue($value, $id = '')
    {
        $qq = [];
        $phone = [];
        $weixin = [];

        $qv = isset($value['qq']) ? $value['qq'] : '';
        $pv = isset($value['phone']) ? $value['phone'] : '';
        $wv = isset($value['weixin']) ? $value['weixin'] : '';

        if($result = $this->deepCheck($qv, $qq, $id, $value)){

            return $result;
        }else if($result = $this->deepCheck($pv, $phone, $id, $value)){

            return $result;
        }else if($result = $this->deepCheck($wv, $weixin, $id, $value)){

            return $result;
        }
    }

    /**
     * 应用场景：深度查询
     * @param string $value    精准字段
     * @param array $key       索引数组
     * @param id $id           更新id
     * @param array $data      提交数据
     * @return array|bool
     */
    private function deepCheck($value, $key, $id, $data)
    {
        if($value != ''){

            $key[] = ['qq|weixin|phone', 'EQ', $value];
            // 查询所有满足条件的数据
            $check = $this->where($key)->select();

            if(count($check) > 0){

                $exist = [];
                // 遍历数据
                foreach ($check as $key => $value) {

                    $user = Admin::field('users')->where('id', 'EQ', $value['uid'])->find();

                    // 若为修改同时满足id不为空且跳过当前操作的列 。或同时满足查询的数列的操作人不为当前操作者
                    if(!empty($id) && $value['id'] != $id){

                        $exist[$key] = array(
                            'id' => $value['id'],
                            'user' => $user['users'],
                            'uid' => $value['uid']
                        );
                    }else if($value['uid'] != $data['uid']){

                        $exist[$key] = array(
                            'id' => $value['id'],
                            'user' => $user['users'],
                            'uid' => $value['uid']
                        );
                    }
                }
                if(count($exist) > 0){
                    // 默认返回第一个错误表达式
                    return $exist[0];
                }
            }else {
                return false;
            }
        }
    }


    /**
     * 应用场景：自定义软删除
     * @param kid,uid
     * @return json
     */
    public function softDelete($kid = '', $uid)
    {
        if(!empty($kid)){

            $data = $this->find($kid);

            if($data['uid'] == $uid){

                $result = $this->where('id', 'eq', $kid)->update(array('is_delete' => '1'));
                if($result){

                    return ['code' => ReturnCode::SUCCESS, 'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
                }else{

                    return ['code' => ReturnCode::ERROR, 'msg' => Tools::errorCode(ReturnCode::ERROR)];
                }
            }else {

                return ['code' => ReturnCode::OCCUPIED, 'msg' => Tools::errorCode(ReturnCode::OCCUPIED)];
            }
        }else {

            return ['code' => ReturnCode::LACKOFPARAM, 'msg' => Tools::errorCode(ReturnCode::LACKOFPARAM)];
        }
    }


    /**
     * 应用场景：API自定义搜索信息
     * @param kid,uid
     * @return array
     */
     public function search($data,$uid)
     {
         $where = [];
         $where[] = ['uid', 'EQ', $uid];
         $where[] = ['is_delete', 'EQ', 0];

         $start = isset($data['start']) ? $data['start'] : '';
         $end = isset($data['end']) ? $data['end'] : '';
         $key = isset($data['key']) ? $data['key'] : '';
         $type = isset($data['type']) ? $data['type'] : '';
         $note = isset($data['note']) ? $data['note'] : '';

         if($start != '' || $end != '' || ($key != '' && $type != '') || $note != ''){

             //check time
             if ($start && $end) {
                 $where[] = ['newtime', 'between', [$start, $end]];
             }elseif( $start){
                 $where[] = ['newtime', 'GT', $start];
             }elseif ($end) {
                 $where[] = ['newtime', 'LT', $end];
             }

             // check type查询key
             if($type == 'all' || $type == ''){

                 $where[] = ['phone|weixin|qq', 'eq', $key];
             }else if($type == 'qq' || $type == 'phone' || $type == 'weixin'){

                 $where[] = [$type, 'eq', $key];
             }
             if($note != ''){

                 $where[] = ['note', 'LIKE', $note];
             }
         }
         $list = $this->field('id,username,sex,calendar,birthday,qq,phone,weixin,note,newtime,address')->where($where)->order('id desc')->paginate();

         $pages = $list->render();

         return $retult = [
             'data' => $list,
             'pages' => $pages
         ];
     }

     /**
      * 未登录查询
      * @param string $value 关键字
      * @return array
      */
     public function publicSearch($value)
     {
         $where = [];
         if($value && is_numeric($value)){

             $where[] = ['a.phone|a.weixin|a.qq', 'eq', $value];

             $result = $this->alias('a')
             ->field('b.users,b.qq1')
             ->join('admin b','a.uid = b.id')
             ->where($where)->find();

             if($result != null){
                 return ['code' => ReturnCode::SUCCESS, 'msg' => Tools::errorCode(ReturnCode::SUCCESS), 'data' => $result];
             }else {
                 return ['code' => ReturnCode::NODATA, 'msg' => Tools::errorCode(ReturnCode::NODATA), 'data' => []];
             }
         }else {
             return ['code' => ReturnCode::LACKOFPARAM, 'msg' => Tools::errorCode(ReturnCode::LACKOFPARAM),'data' => []];
         }
     }

     /**
      * 后台自定义搜索信息
      * @param array $data 数据集
      * @param Boolean $isPost 是否post提交
      * @param int $uid 用户id标识
      * @param Boolean $isdelete 是否删除
      * @return array
      */
     public function adminSearch($data = '', $isPost = 'false', $uid, $isdelete = 'false')
     {
         // 预定义type 数组
         $checktype = array('qq', 'phone', 'weixin');
         $where = [];
         // 默认取出当天范围内的客户
         // $where[] = ['newtime', 'between', [date('Y-m-d', time()), date('Y-m-d H:i:s', time())]];
         if($isPost  = 'true'){

             $where = [];
             // 接收参数
             $start = isset($data['start']) ? $data['start'] : '';
             $end = isset($data['end']) ? $data['end'] : '';
             $type = isset($data['type']) ? $data['type'] : '';
             $keyword = isset($data['keyword']) ? $data['keyword'] : '';

             //check time
             if ($start && $end) {
                 $where[] = ['a.newtime', 'between', [$start, $end]];
             }elseif($start){
                 $where[] = ['a.newtime', 'GT', $start];
             }elseif ($end) {
                 $where[] = ['a.newtime', 'LT', $end];
             }

             // check type
             if (!empty($keyword)) {
                 if($type && in_array($type, $checktype)){
                     $where[] = ['a.'.$type,  'EQ',  $keyword];
                 }else{
                     $where[] = ['a.qq|a.phone|a.weixin',  'EQ',  $keyword];
                 }
             }
         }

         if($isdelete == 'true'){

             $where[] = ['a.is_delete', 'EQ', '1'];
         }else{

             $where[] = ['a.is_delete', 'EQ', '0'];
         }

         if(!checksuperman($uid)){
             $where[] = ['a.uid', 'EQ', $uid];
         }
         $list = $this->alias('a')
         ->field('a.*, b.users')
         ->join('admin b', 'a.uid = b.id')
         ->where($where)->order('id desc')->paginate();
         $count = $list->total();

         return $result = [
             'list' => $list,
             'count'=>$count,
         ];
     }


     /**
      * 检测数据。并批量插入数据库
      * @return success(成功条数) error(失败条数)
      */
     public function checkdata($file, $fields, $filename, $uid)
     {
         if($file){
             $qq = [];
             $ph = [];
             $wx = [];

             // 根据字段拼接其键
             foreach($file as $k => $v){

                 foreach($fields as $k1 => $v1) {

                     $data[$k][$v1] = $v[$k1] ? trim(Tools::convertStrType($v[$k1], 'TOSBC')) : '';

                     if($v1 == 'qq'){
                         $qq[$k] = $v[$k1];
                     }else if($v1 == 'phone') {
                         $ph[$k] = $v[$k1];
                     }else if($v1 == 'weixin'){
                         $wx[$k] = $v[$k1];
                     }

                 }

                 $data[$k]['uid'] = $uid;
                 $data[$k]['newtime'] = date('Y-m-d H:i:s', time());
             }
             // -------------------检测数据完整性, 对数据去重，检测数据库是否存在
             $have = [];
             // 验证数据合法性
             foreach($data as $k => $v){

                 if(strlen($v['qq']) < 5 || !is_numeric($v['qq']) || strlen($v['qq']) > 11){

                     $have[] = $k;
                 }
                 if(!is_numeric($v['qq']) || strlen($v['phone']) != 11){

                     $have[] = $k;
                 }
                 // 正则验证手机号
                 if(preg_match('/^1[3-8]{1}[0-9]{9}$/',$v['phone']) == 0){
                     $have[] = $k;
                 }
             }
             // 去重
             $have = isset($qq) ? $this->checkUnique($qq, $have) : $have;
             $have = isset($ph) ? $this->checkUnique($ph, $have) : $have;
             $have = isset($wx) ? $this->checkUnique($wx, $have) : $have;
             // 读取一次数据库所有数据
             $model = new Member;

             if(count($qq) > 0){
                 $haveqq = $model->field('qq, phone, weixin')->where('qq', 'in', $qq)->select();

                 $have = $this->arraySearch($haveqq, $qq, $have, 'qq');
             }

             if(count($ph) > 0){
                 $haveph = $model->field('qq, phone, weixin')->where('phone', 'in', $ph)->select();

                 $have = $this->arraySearch($haveph, $ph, $have, 'phone');
             }

             if(count($wx) > 0){
                 $havewx = $model->field('qq, phone, weixin')->where('weixin', 'in', $wx)->select();

                 $have = $this->arraySearch($havewx, $wx, $have, 'weixin');
             }

             // 如果已经存在，返回键
             $have = array_unique($have);

             // -------------------对合法数据进行重新排列，清理已存在，不合法数据
             $errors = count($have);

             if($errors > 0){

                 $error = Cache::get('scv_'.$uid.'_'.$filename) ? Cache::get('scv_'.$uid.'_'.$filename) : [];

                 foreach($have as $k => $v){
                     
                     array_push($error, $data[$v]);

                     unset($data[$v]);
                 }

                 Cache::set('scv_'.$uid.'_'.$filename, $error);
             }

             // 不存在数据的不存入日志
             if(!empty($data)){

                 // ------日志处理 ---start
                 writelog($data, Tools::logactKey('delete_import'), $uid);
                 // -------------------end
             }
             // -------------------批量新增数据

             $success = $model->limit(100)->insertAll($data);

             return array(
                 'success' => $success ? $success : 0,
                 'error'   => $errors ? $errors : 0,
             );
         }
     }

     /**
      * 检测数据。深度去重~！
      * @return have 重复的键名
      */
     private function checkUnique($data,$have){

         $key = isset($data) ? $data : [];

         if(!empty($key)){

             $newkey = array_unique($key);

             foreach (array_flip($data) as $key => $value) {

                 if(!in_array($value, array_flip($newkey))){

                     if(!in_array($value, $have)){

                         $have[] = $value;
                     }
                 }
             }
             return $have;
         }
         return $have;
     }


     /**
      * 根据值查询数组返回键
      */
     private function arraySearch($exist, $array, $have, $ke)
     {
         foreach ($exist as $key => $value) {

             $k = array_search($value[$ke], $array);

             // 避免键为0被误伤到
             if($k == '0' || $k != false){

                 if(!in_array($k, $have)){

                     $have[] = $k;
                 }
             }
         }
         return $have;
     }
}
