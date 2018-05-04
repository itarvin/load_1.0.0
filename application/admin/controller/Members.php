<?php
namespace app\admin\controller;
/**
 * 后台客户处理类
 * @author  itarvin itarvin@163.com
 */
use app\model\Member;
use think\File;
use think\facade\Cache;
use app\util\ReturnCode;
use app\model\Record;
use app\util\Tools;
use think\facade\Request;
class Members extends Base{
    /**
     * 主页显示
     * 获取当前管理员的客户会员
     */
    public function index()
    {
        $member = new Member;
        // 预定义type 数组
        $checktype = array('qq', 'phone', 'weixin');
        $where = [];
        $where[] = ['a.is_delete', 'EQ', '0'];
        // check Admin
        if($this->superman != 'yes'){
            $where[] = ['a.uid', 'EQ', $this->uid];
        }
        // 默认取出当天范围内的客户
        // $where[] = ['newtime', 'between', [date('Y-m-d', time()), date('Y-m-d H:i:s', time())]];
        if(request()->isPost())
        {
            $where = [];
            // 接收参数
            $start = Request::param('start', '', 'trim');
            $end = Request::param('end', '', 'trim');
            $type = Request::param('type', '', 'trim');
            $keyword = Request::param('keyword', '', 'trim');

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
        $list = $member->alias('a')
        ->field('a.*, b.users')
        ->join('admin b', 'a.uid = b.id')
        ->where($where)->order('id desc')->paginate();
        $count = $list->total();
        $this->assign([
            'list' => $list,
            'count'=>$count,
            'uid'  => $this->uid,
            'superman'  => $this->superman
        ]);
        return $this->fetch('Members/index');
    }


    /**
     * 批量回收
     * @return json
     */
    public function batchdelete()
    {
        $data = input('post.');
        $member = new Member;
        $nums = 0;
        foreach($data['deleid'] as $k => $v){
            $have = $member->where('id', $v)->find();
            if($have['uid'] != $this->uid){
                $nums += 1;
            }
        }
        if($nums >= 1){
            $return['status'] = ReturnCode::ERROR;
            $return['error']  = $nums;
        }else {
            $count = count($data['deleid']);
            $num = 0;
            foreach($data['deleid'] as $k => $v){
                // 放进回收站暂不做日志处理，和删除相关订单信息
                // ------日志处理 ---start
                // writelog($v, Tools::logactKey('cus_delete'), $this->uid);
                // -------------------end
                // $del = $this->shiftCustom($v);
                $re = $member->where('id', $v)->update(array('is_delete' => 1));
                if($re){
                    $num += 1;
                }
            }
            if($count == $num){
                $return['status'] = ReturnCode::SUCCESS;
                $return['success'] = $num;
            }else {
                $return['status'] = ReturnCode::SUCCESS;
                $return['error'] = ($count - $num);
            }
        }
        return json($return);
    }


    /**
     * 删除客户清除消费记录
     */
    private function shiftCustom($memberid)
    {
        $list = Record::field('id')->where('khid', 'EQ', $memberid)->select();
        foreach ($list as $key => $value) {
            Record::where('id', $value['id'])->delete();
        }
    }


    /**
     * 单条删除
     * @return json
     */
    public function delete()
    {
        $id = input('post.deleid');
        $member = new Member;
        // 先判断当前是删除数据是否为本人的客户。
        $have = $member->where('id', $id)->find();
        if($have['uid']  != $this->uid){
            return buildReturn(['status' => ReturnCode::ERROR,'info'=> '当前客户非您的客户！']);
        }else {
            $this->shiftCustom($id);
            // ------日志处理 ---start
            writelog($id, Tools::logactKey('cus_delete'), $this->uid);
            // -------------------end
            $re = $member->where('id', $id)->delete();
            if($re){
                return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS)]);
            }
        }
    }



    /**
     * 回收站恢复
     * @return json
     */
    public function renew()
    {
        $id = input('post.newid');
        $member = new Member;
        // 先判断当前是删除数据是否为本人的客户。
        $have = $member->where('id', $id)->find();
        if($have['uid']  != $this->uid){
            return buildReturn(['status' => ReturnCode::ERROR,'info'=> '当前客户非您的客户！']);
        }else {
            $re = $member->where('id', $id)->update(array('is_delete' => '0'));
            if($re){
                return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS)]);
            }else{
                return buildReturn(['status' => ReturnCode::ERROR,'info'=> Tools::errorCode(ReturnCode::ERROR)]);
            }
        }
    }

    /**
     * 添加操作
     */
    public function add(){
        return $this->fetch('Members/add');
    }

    /**
     * 下载CSV文件模板
     */
    public function downMould(){
        $field = config('upload_field');
        Cache::set('scv_field', $field);
        $data = Tools::fieldMapped($field);
        ini_set("max_execution_time",  "3600");
        $csv_data = '';
        /** 标题 */
        $nums = count($data);
        for ($i = 0; $i < $nums - 1; ++$i) {
            $csv_data .= $data[$i] . ', ';
        }
        if ($nums > 0) {
            $csv_data .= $data[$nums - 1] . "\r\n";
        }
        $csv_data = mb_convert_encoding($csv_data,  "cp936",  "UTF-8");
        $file_name = '客户上传模板';
        // 解决IE浏览器输出中文名乱码的bug
        if(preg_match( '/MSIE/i',  $_SERVER['HTTP_USER_AGENT'] )){
            $file_name = urlencode($file_name);
            $file_name = iconv('UTF-8',  'GBK//IGNORE',  $file_name);
        }
        $file_name = $file_name . '.csv';
        header('Content-Type: application/download');
        header("Content-type:text/csv;");
        header("Content-Disposition:attachment;filename=" . $file_name);
        header('Cache-Control:must-revalidate, post-check=0, pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $csv_data;
        exit();
    }


    /**
     * 检测CSV文件是否上传
     * @param string $way csv文件路径
     * @param string $value 根据值返回键
     * @param array $array 键值对集合
     * @return josn
     */
    public function loadmember()
    {
        $way = config('upload_path').'/custom';
        $file = request()->file('file');
        $fields = Cache::get('scv_field') ? Cache::get('scv_field') : config('upload_field');
        $maxItem = config('maxitem');
        // 如果文件太大无法上传。则会提示null
        if($file != null){
            $info = $file->rule('uniqid')->move($way);
            $filename = $info->getSaveName();
            // 限制文件类型
            $type = explode(".", $filename);
            if($info && $type[1] == 'csv'){
                Cache::set('scv_'.$this->uid, $filename);
                $line = count(file($way.'/'.$filename));
                if($line > 1){
                    $dealLine = $line > $maxItem ? $maxItem : $line;
                    // 若文件行数大于1000行。则先处理1000行，返回前端回调处理剩下的数据
                    $start = 0;
                    $content = Tools::read_csv_lines($way.'/'.$filename, $dealLine, $start);
                    if($content){
                        // 执行检测和插入数据
                        $result = $this->checkdata($content, $fields, $filename);
                        $data = [
                            'status'  => ReturnCode::SUCCESS,
                            'success' => $result['success'],
                            'error'   => $result['error'],
                            'deal'    => $result['success'] + $result['error'],
                            'total'   => ($line - 1)
                        ];
                    }
                }else {
                    $data['status'] = ReturnCode::ERROR;
                }
            }else {
                $data['status'] = ReturnCode::ERROR;
            }
        }else {
            $data['status'] = ReturnCode::ERROR;
        }
        return json($data);
    }


    /**
     * 检测数据。并批量插入数据库
     * @return success(成功条数) error(失败条数)
     */
    private function checkdata($file, $fields, $filename)
    {
        if($file){
            $qq = [];
            $ph = [];
            $wx = [];
            // 根据字段拼接其键
            foreach($file as $k => $v) {
                foreach($fields as $k1 => $v1) {
                    $data[$k][$v1] = $v[$k1] ? Tools::convertStrType($v[$k1], 'TOSBC') : '';
                    if($v1 == 'qq'){
                        $qq[$k] = $v[$k1];
                    }else if($v1 == 'phone') {
                        $ph[$k] = $v[$k1];
                    }else if($v1 == 'weixin'){
                        $wx[$k] = $v[$k1];
                    }
                }
                $data[$k]['uid'] = $this->uid;
                $data[$k]['newtime'] = date('Y-m-d H:i:s', time());
            }
            // -------------------检测数据完整性, 对数据去重，检测数据库是否存在
            $have = [];
            // 验证数据合法性
            foreach($data as $k => $v){
                if(strlen($v['qq']) < 5 || is_numeric($v['qq'] || is_numeric($v['phone']) || strlen($v['qq']) > 11 || strlen($v['phone'] != 11))){
                    $have[] = $k;
                }
            }
            // 去重
            $have = isset($qq) ? $this->checkUnique($qq,$have) : $have;
            $have = isset($ph) ? $this->checkUnique($ph,$have) : $have;
            $have = isset($wx) ? $this->checkUnique($wx,$have) : $have;
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
            array_unique($have);
            // -------------------对合法数据进行重新排列，清理已存在，不合法数据
            $errors = count($have);
            if($errors > 0){
                $error = Cache::get('scv_'.$this->uid.'_'.$filename) ? Cache::get('scv_'.$this->uid.'_'.$filename) : [];
                foreach($have as $k => $v){
                    array_push($error, $data[$v]);
                    unset($data[$v]);
                }
                Cache::set('scv_'.$this->uid.'_'.$filename, $error);
            }
            // 不存在数据的不存入日志
            if(!empty($data)){
                // ------日志处理 ---start
                writelog($data, Tools::logactKey('delete_import'), $this->uid);
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
     * 检测CSV文件与存在数据有哪些已存在
     * @param string $way csv文件路径
     * @return json
     */
    public function batchMember(){
        $model = new Member;
        $maxItem = config('maxitem');
        $success = Request::param('success', '', 'trim');
        $error = Request::param('error', '', 'trim');
        $deals = Request::param('deal', '', 'trim');
        // 拼接文件路径
        $way = config('upload_path').'/custom';
        $filename = Cache::get('scv_'.$this->uid);
        $fields = Cache::get('scv_field') ? Cache::get('scv_field') : config('upload_field');
        $line = count(file($way.'/'.$filename));
        // 起始行根据客户端处理了多少数据
        $start = $deals ? $deals : $maxItem;
        // 读取行数，判断总行数减去已经处理的，如还大于系统最大处理数，则只处理系统最大处理数，反之处理剩余的条数
        $dealLine = ($line - $start) > $maxItem ? $maxItem : ($line - $start - 1);
        $deal = ($dealLine == $maxItem) ? $maxItem : ($line - $start);
        $content = Tools::read_csv_lines($way.'/'.$filename, $dealLine, $start);
        if($content){
            $result = $this->checkdata($content, $fields, $filename);
            // 最后一次执行文件后删除文件
            if($deal < $maxItem){
                unlink($way.'/'.$filename);
            }
            $data = [
                'status'  => ReturnCode::SUCCESS,
                'success' => ($result['success'] + $success),
                'error'   => ($result['error'] + $error),
                'deal'    => ($result['success'] + $success) + ($result['error'] + $error),
                'total'   => ($line-1),
            ];
        }else {
            $data['status'] = ReturnCode::ERROR;
        }
        return json($data);
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


    /**
     * 回收站
     */
    public function recycle()
    {
        $member = new Member;
        // 预定义type 数组
        $checktype = ['qq, phone, weixin'];
        $where = [];
        $where[] = ['is_delete', 'EQ', '1'];
        if($this->uid != '1'){
            $where[] = ['uid', 'EQ', $this->uid];
        }
        if(request()->isPost()){
            $start = Request::param('start', '', 'trim');
            $end = Request::param('end', '', 'trim');
            $type = Request::param('type', '', 'trim');
            $keyword = Request::param('keyword', '', 'trim');
            //check time
            if ($start && $end) {
                $where[] = ['newtime', 'between', [$start, $end]];
            }elseif($start){
                $where[] = ['newtime', 'GT', $start];
            }elseif ($end) {
                $where[] = ['newtime', 'LT', $end];
            }
            // check type
            if (!empty($keyword)) {
                if($type && in_array($type, $checktype)){
                    $where[] = [$type,  'EQ',  $keyword];
                }else{
                    $where[] = ['qq|phone|weixin',  'EQ',  $keyword];
                }
            }
        }
        $list = $member->where($where)->paginate();
        $count = $list->total();
        $this->assign([
            'list' => $list,
            'count'=>$count,
            'uid'  => $this->uid,
        ]);
        return $this->fetch('Members/recycle');
    }


    /**
     * 修改静态页
     */
    public function edit()
    {
        $id = Request::param('id', '', 'trim');
        $consumer = new Member;
        $data = $consumer->find($id);
        if($this->uid != 1 && $data['uid'] != $this->uid){
            $this->error('对不起，非法访问！');
        }
        $this->assign('data', $data);
        return $this->fetch('members/edit');
    }

    /**
     * 更新数据
     * @return json
     */
    public function update()
    {
        if(request()->isPost()){
            $member = new Member;
            // 接收所有参数
            $data = Request::param();
            if($this->uid != 1 && $data['uid'] != $this->uid){
                $this->error('对不起，非法访问！');
            }
            $data['uid'] = $this->uid;
            $result = $member->store($data);
            return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
        }
    }
}
