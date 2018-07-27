<?php
namespace app\admin\controller;
/**
 * 应用场景：后台客户处理类
 * @author  itarvin itarvin@163.com
 */
use app\model\Member as memberModel;
use think\File;
use think\facade\Cache;
use app\Util\ReturnCode;
use app\model\Record;
use app\Util\Tools;
class Member extends Base
{

    /**
     * 应用场景：主页显示
     * 获取当前管理员的客户会员
     */
    public function index()
    {
        $member = new memberModel;
        $result = $member->adminSearch('','false',$this->uid,'false');
        if($this->request->isPost()){
            $result = $member->adminSearch($this->request->param(),'true',$this->uid,'false');
        }
        $this->assign([
            'list'   => $result['list'],
            'count'  => $result['count'],
            'uid'    => $this->uid,
        ]);
        return $this->fetch('');
    }

    /**
     * 应用场景：添加操作
     */
    public function add(){
        return $this->fetch('');
    }

    /**
     * 应用场景：修改静态页
     */
    public function edit()
    {
        $id = $this->request->param('id', '', 'trim');
        $consumer = new memberModel;
        $data = $consumer->find($id);
        if($data['uid'] != $this->uid){
            $this->error('对不起，非法访问！');
        }
        if($this->request->isPost()){
            $member = new memberModel;
            // 接收所有参数
            $data = $this->request->param();
            if($this->uid != 1 && $data['uid'] != $this->uid){
                $this->error('非您的客户！');
            }
            $data['uid'] = $this->uid;
            $result = $member->store($data);
            return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
        }
        $this->assign('data', $data);
        return $this->fetch('');
    }

    /**
    * 应用场景： 单条删除
    * @return json
    */
    public function delete()
    {
        $id = $this->request->param('kid', '', 'tirm');
        $member = new memberModel;
        // 先判断当前是删除数据是否为本人的客户。
        $have = $member->where('id', $id)->find();
        if($have['uid']  != $this->uid){
            return buildReturn(['status' => ReturnCode::ERROR,'info'=> '当前客户您无法操作！']);
        }else {
            $this->shiftCustom($id);
            // ------日志处理 ---start
            writelog($id, Tools::logactKey('cus_delete'), $this->uid);
            // -------------------end
            if($member->where('id', $id)->delete()){
                return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS)]);
            }else {
                return buildReturn(['status' => ReturnCode::ERROR,'info'=> Tools::errorCode(ReturnCode::ERROR)]);
            }
        }
    }

    /**
     * 回收站
     */
    public function recycle()
    {
        $member = new memberModel;
        $result = $member->adminSearch('','false',$this->uid,'true');
        if($this->request->isPost()){
            $result = $member->adminSearch($this->request->param(),'true',$this->uid,'true');
        }
        $this->assign([
            'list'   => $result['list'],
            'count'  => $result['count'],
            'uid'    => $this->uid,
        ]);
        return $this->fetch('');
    }

    /**
     * 批量回收
     * @return json
     */
    public function batchdelete()
    {
        $data = $this->request->param();
        $member = new memberModel;
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
     * 回收站恢复
     * @return json
     */
    public function renew()
    {
        $id = $this->request->param("kid","",'trim');
        $member = new memberModel;
        // 先判断当前是删除数据是否为本人的客户。
        $have = $member->where('id', $id)->find();
        if($have && $have['uid'] != $this->uid){
            return buildReturn(['status' => ReturnCode::ERROR,'info'=> '当前客户非您的客户！']);
        }else {
            if($member->where('id', $id)->update(array('is_delete' => '0'))){
                return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS)]);
            }else{
                return buildReturn(['status' => ReturnCode::ERROR,'info'=> Tools::errorCode(ReturnCode::ERROR)]);
            }
        }
    }

    /**
     * 下载CSV文件模板
     */
    public function downMould()
    {
        $field = getconf('upload_field');
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
        $file = $this->request->file('file');
        $fields = explode(",", str_replace("，",",",getconf('upload_field')));
        $maxItem = getconf('maxItem');
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
                        $model = new memberModel;
                        // 执行检测和插入数据
                        $result = $model->checkdata($content, $fields, $filename, $this->uid);
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
     * 检测CSV文件与存在数据有哪些已存在
     * @param string $way csv文件路径
     * @return json
     */
    public function batchMember()
    {
        $model = new memberModel;
        $maxItem = getconf('maxItem');
        $success = $this->request->param('success', '', 'trim');
        $error = $this->request->param('error', '', 'trim');
        $deals = $this->request->param('deal', '', 'trim');
        // 拼接文件路径
        $way = config('upload_path').'/custom';
        $filename = Cache::get('scv_'.$this->uid);
        $fields = explode(",", str_replace("，",",",getconf('upload_field')));
        $line = count(file($way.'/'.$filename));
        // 起始行根据客户端处理了多少数据
        $start = $deals ? $deals : $maxItem;
        // 读取行数，判断总行数减去已经处理的，如还大于系统最大处理数，则只处理系统最大处理数，反之处理剩余的条数
        $dealLine = ($line - $start) > $maxItem ? $maxItem : ($line - $start - 1);
        $deal = ($dealLine == $maxItem) ? $maxItem : ($line - $start);
        $content = Tools::read_csv_lines($way.'/'.$filename, $dealLine, $start);
        if($content){
            $result = $model->checkdata($content, $fields, $filename, $this->uid);
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
}
