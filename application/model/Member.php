<?php
namespace app\model;
use think\Model;
use think\Validate;
use app\util\ReturnCode;
use app\util\Tools;
use app\model\Admin;
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
     * 新增，修改数据时的数据验证与处理，日志添加
     * @param string $data    所有数据
     * @return array
     */
    public function store($data)
    {
        // 先检测当前数据是否存在其他行列
        $id = isset($data['id']) ? $data['id'] : '';
        // echo 'stert'.date('Y-m-d H:i:s',time());
        $result = $this->checkValue($data, $id);
        // echo 'end'.date('Y-m-d H:i:s',time());die;
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
     * 检测是否QQ，phone，weixin其余列是否存在当前值
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
        // echo 'stert'.date('Y-m-d H:i:s',time());
        // $result = $this->deepCheck($wv, $weixin, $id, $value);
        // echo 'end'.date('Y-m-d H:i:s',time());die;
        if($result = $this->deepCheck($qv, $qq, $id, $value)){
            return $result;
        }else if($result = $this->deepCheck($pv, $phone, $id, $value)){
            return $result;
        }else if($result = $this->deepCheck($wv, $weixin, $id, $value)){
            return $result;
        }
    }

    /**
     * 深度查询
     * @param string $value    精准字段
     * @param array $key       索引数组
     * @param id $id           更新id
     * @param array $data      提交数据
     * @return array|bool
     */
    private function deepCheck($value, $key, $id, $data){
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
     * 自定义软删除
     * @param kid,uid
     * @return json
     */
    public function softDelete($kid = '', $uid){
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
     * 自定义搜索信息
     * @param kid,uid
     * @return array
     */
     public function search($data,$uid){
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
}
