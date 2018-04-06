<?php
namespace app\admin\controller;
use app\admin\model\Consumer;
use think\File;
use think\facade\Cache;
use app\util\ReturnCode;
use app\util\Tools;

class Member extends Base
{
    /**
     * 主页显示
     */
    public function index()
    {
        $member = new Consumer;
        // 获取当前管理员的客户会员
        if(request()->isPost())
        {
            $data = input('post.');
            $start = $data['start'] ? trim($data['start']) : '';
            $end = $data['end'] ? trim($data['end']) : '';
            $keyword = $data['keyword'] ? trim($data['keyword']) : '';
            $keywordComplex = [];
            if (!empty($keyword)) {
                $keywordComplex[] = ['qq|phone|weixin|note','like',"%".$keyword."%"];
            }
            $where = array();
            if ($start && $end) {
                $where[] = ['newtime','between',[$start,$end]];
            }elseif($start){
                $where[] = ['newtime','GT',$start];
            }elseif ($end) {
                c
            }

            if($this->uid != 1)
            {
                $list = $member->order('id desc')->where(array('uid' => $this->uid))
                ->where($keywordComplex)->where($where)->paginate();
                $count = $member->where(array('uid' => $this->uid))
                ->where($keywordComplex)->where($where)->count();
            }else {
                $list = $member->order('id desc')->where($keywordComplex)->where($where)->paginate();
                $count = $member->where($keywordComplex)->where($where)->count();
            }
        }else {
            if($this->uid != 1)
            {
                $list = $member->order('id desc')->where(array('uid' => $this->uid))->paginate(10);
                $count = $member->where(array('uid' => $this->uid))->count();
            }else {
                $list = $member->order('id desc')->paginate(10);
                $count = $member->count();
            }
        }
        $this->assign('list',$list);
        $this->assign('count',$count);
        $this->assign('uid',$this->uid);
        return $this->fetch('Member/index');
    }


    // 批量删除
    public function batchdelete()
    {
        $data = input('post.');
        $member = new Consumer;
        // 先判断当前是删除数据是否为本人的客户。
        $nums = 0;
        foreach($data['deleid'] as $k => $v){
            $have = $member->where('id',$v)->find();
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
                $re = $member->where('id',$v)->delete();
                if($re){
                    $num += 1;
                }
            }
            if($count == $num){
                $return['status'] = ReturnCode::SUCCESS;;
                $return['success'] = $num;
            }else {
                $return['status'] = ReturnCode::SUCCESS;;
                $return['error'] = ($count - $num);
            }
        }
        return json($return);
    }



    public function delete()
    {
        $id = input('post.deleid');
        $member = new Consumer;
        // 先判断当前是删除数据是否为本人的客户。
        $have = $member->where('id',$id)->find();
        if($have['uid']  != $this->uid)
        {
            $data['status'] = ReturnCode::ERROR;
            $data['msg'] = '当前客户非您的客户！';
        }else {
            $re = $member->where('id',$id)->delete();
            if($re){
                $data['status'] = ReturnCode::SUCCESS;;
            }
        }
        return json($data);
    }

    /**
     * 添加操作
     */
    public function add()
    {
        return $this->fetch('Member/add');
    }

    /**
     * 下载CSV文件模板
     */
    public function downMould()
    {
        $field = config('upload_field');
        Cache::set('scv_field',$field);
        $data = Tools::fieldMapped($field);
        ini_set("max_execution_time", "3600");
        $csv_data = '';
        /** 标题 */
        $nums = count($data);
        for ($i = 0; $i < $nums - 1; ++$i) {
            $csv_data .= $data[$i] . ',';
        }
        if ($nums > 0) {
            $csv_data .= $data[$nums - 1] . "\r\n";
        }
        $csv_data = mb_convert_encoding($csv_data, "cp936", "UTF-8");
        $file_name = '客户上传模板';
        // 解决IE浏览器输出中文名乱码的bug
        if(preg_match( '/MSIE/i', $_SERVER['HTTP_USER_AGENT'] )){
            $file_name = urlencode($file_name);
            $file_name = iconv('UTF-8', 'GBK//IGNORE', $file_name);
        }
        $file_name = $file_name . '.csv';
        header('Content-Type: application/download');
        header("Content-type:text/csv;");
        header("Content-Disposition:attachment;filename=" . $file_name);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
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
        // 如果文件太大。则会提示null
        if($file != null)
        {
            $info = $file->rule('uniqid')->move($way);
            $filename = $info->getSaveName();
            // 限制文件类型
            $type = explode(".",$filename);
            if($info && $type[1] == 'csv')
            {
                Cache::set('scv_'.$this->uid,$filename);
                $line = count(file($way.'/'.$filename));
                if($line > 1)
                {
                    $dealLine = $line > 1001 ? 1001 : $line;
                    // 若文件行数大于1000行。则先处理1000行，返回前端回调处理剩下的数据
                    $start = 0;
                    $content = Tools::read_csv_lines($way.'/'.$filename,$dealLine,$start);
                    if($content){
                        $result = $this->checkdata($content,$fields,$filename);
                        $data = array(
                            'status'  => ReturnCode::SUCCESS,
                            'success' => $result['success'],
                            'error'   => $result['error'],
                            'deal'    => ($dealLine - 1),
                            'total'   => ($line - 1)
                        );
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



    private function checkdata($file,$fields,$filename)
    {
        if($file){
            // 根据字段拼接其键
            foreach($file as $k => $v) {
                foreach($fields as $k1 => $v1) {
                    $data[$k][$v1] = $v[$k1] ? Tools::convertStrType($v[$k1],'TOSBC') : '';
                    if($v1 == 'qq'){
                        $qq[$k] = $v[$k1];
                    }else if($v1 == 'phone') {
                        $ph[$k] = $v[$k1];
                    }
                }
                $data[$k]['uid'] = $this->uid;
                $data[$k]['newtime'] = date('Y-m-d H:i:s',time());
            }
            // -------------------检测数据完整性,对数据去重，检测数据库是否存在
            $have = [];
            // 验证数据合法性
            foreach($data as $k => $v){
                if(strlen($v['qq']) < 5 || is_numeric($v['qq'] || is_numeric($v['phone']) || strlen($v['qq']) > 10 || strlen($v['phone'] != 11))){
                    $have[] = $k;
                }
            }
            // 去重
            $newqq = array_unique($qq);
            $newph = array_unique($ph);
            foreach (array_flip($qq) as $key => $value) {
                if(!in_array($value,array_flip($newqq))){
                    if(!in_array($value,$have)){
                        $have[] = $value;
                    }
                }
            }
            foreach (array_flip($ph) as $key => $value) {
                if(!in_array($value,array_flip($newph))){
                    if(!in_array($value,$have)){
                        $have[] = $value;
                    }
                }
            }
            // 读取一次数据库所有数据
            $model = new Consumer;
            $haveqq = $model->field('qq,phone')->where('qq','in',$qq)->select();
            $haveph = $model->field('qq,phone')->where('phone','in',$ph)->select();
            // 如果已经存在，返回键
            $have = $this->arraySearch($haveqq,$qq,$have,'qq');
            $have = $this->arraySearch($haveph,$ph,$have,'phone');
            array_unique($have);
            // -------------------对合法数据进行重新排列，清理已存在，不合法数据
            $errors = count($have);
            if($errors > 0){
                $error = Cache::get('scv_'.$this->uid.'_'.$filename) ? Cache::get('scv_'.$this->uid.'_'.$filename) : array();
                foreach($have as $k => $v)
                {
                    array_push($error,$data[$v]);
                    unset($data[$v]);
                }
                Cache::set('scv_'.$this->uid.'_'.$filename,$error);
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
     * 检测CSV文件与存在数据有哪些已存在
     * @param string $way csv文件路径
     * @return json
     */
    public function batchMember()
    {
        $model = new Consumer;
        $input = input('post.');
        // 拼接文件路径
        $way = config('upload_path').'/custom';
        $filename = Cache::get('scv_'.$this->uid);
        $fields = Cache::get('scv_field');
        $line = count(file($way.'/'.$filename));
        $maxItem=500;
        // 起始行根据客户端处理了多少数据
        $start = $input['deal'] ? $input['deal'] : $maxItem;
        // 读取行数，判断总行数减去已经处理的，如还大于1000，则只处理1000，反之处理剩余的条数
        $dealLine = ($line - $start) > 1001 ? 1001 : $line - $start;
        $deal = ($dealLine == 1001) ? ($dealLine - 1) : ($line - $start - 1);
        $content = Tools::read_csv_lines($way.'/'.$filename,$dealLine,$start);
        if($content){
            $result = $this->checkdata($content,$fields,$filename);
            // 最后一次执行文件后删除文件
            if($deal < 1000){
                unlink($way.'/'.$filename);
            }
            $data = array(
                'status'  => ReturnCode::SUCCESS,
                'success' => ($result['success'] + $input['success']),
                'error'   => ($result['error'] + $input['error']),
                'deal'    => $deal + $input['deal'],
                'total'   => ($line - 1),
            );
        }else {
            $data['status'] = ReturnCode::ERROR;
        }
        return json($data);
    }


    private function arraySearch($exist,$array,$have,$ke)
    {
        foreach ($exist as $key => $value) {
            $k = array_search($value[$ke],$array);
            // 避免键为0被误伤到
            if($k == '0' || $k != false){
                if(!in_array($k,$have)){
                    $have[] = $k;
                }
            }
        }
        return $have;
    }
}
