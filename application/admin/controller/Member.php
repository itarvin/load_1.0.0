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
            if($start){
                $where[] = ['newtime','GT',$start];
            }elseif ($end) {
                $where[] = ['newtime','LT',$end];
            }elseif ($start && $end) {
                $where[] = ['newtime','between',[$start,$end]];
            }
            if($this->uid != 1)
            {
                $list = $member->order('id desc')->where(array('uid' => $this->uid))
                ->where($keywordComplex)->where($where)->paginate(10);
                $count = $member->where(array('uid' => $this->uid))
                ->where($keywordComplex)->where($where)->count();
            }else {
                $list = $member->order('id desc')->where($keywordComplex)->where($where)->paginate(10);
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
        $field = array('username','phone','address','note','qq');
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
    public function importMember()
    {
        $way = config('upload_path').'/custom';
        $file = request()->file('file');
        $info = $file->rule('uniqid')->move($way);
        $filname = $info->getSaveName();
        if($info)
        {
            Cache::set('scv_'.$this->uid,$filname);
            $line = count(file($way.'/'.$filname));
            // 获取csv所有的数据存储缓存
            $content = Tools::read_csv_lines($way.'/'.$filname,$line);
            if($content){
                Cache::set('scv_file'.$this->uid,$content);
                $data['status'] = ReturnCode::SUCCESS;
                $data['symbol'] = 'scv_'.$this->uid;
            }else {
                $data['status'] = ReturnCode::NODATA;
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
    public function leadMember()
    {
        $model = new Consumer;
        $symbol = input('symbol');
        $filename = Cache::pull('scv_'.$this->uid);
        // 先删除文件
        unlink(config('upload_path').'/custom/'.$filename);
        $cachefile = 'scv_file'.$this->uid;
        $fields = Cache::get('scv_field');
        // 获取并删除内容缓存
        $file = Cache::pull($cachefile);
        if($file){
        // 根据字段拼接其键
            $pho = [];
            $qq = [];
            foreach ($file as $k => $v) {
                foreach ($fields as $k1 => $v1) {
                    $data[$k][$v1] = $v[$k1] ? Tools::convertStrType($v[$k1],'TOSBC') : '';
                    if($v1 == 'qq'){
                        $qq[] = $v[$k1];
                    }else if($v1 == 'phone'){
                        $pho[] = $v[$k1];
                    }
                }
                $data[$k]['uid'] = $this->uid;
                $data[$k]['newtime'] = date('Y-m-d H:i:s',time());
            }
            // -------------------检测数据完整性
            $have = [];
            foreach($data as $k => $v){
                if(strlen($v['qq']) < 5 || is_numeric($v['qq'] || is_numeric($v['phone']) || strlen($v['qq']) > 10 || strlen($v['phone'] != 11))){
                    $have[] = $k;
                }
            }
            // 读取一次数据库所有数据
            $existData = $model->field('qq,phone')->select();
            // 分离两个字段
            $existQq = [];
            $existPh = [];
            foreach($existData as $k => $v){
                $existQq[] = $v['qq'];
                $existPh[] = $v['phone'];
            }
            // 如果已经存在，返回键
            $have = $this->arraySearch($existQq,$qq,$have);
            $have = $this->arraySearch($existPh,$pho,$have);
            // -------------------对数据清洗
            $errors = count($have);
            if($errors > 0){
                $con = Cache::get('scv_'.$this->uid.'_'.$filename);
                $error =  $con ? $con : [];
                foreach($have as $v){
                    array_push($error,$data[$v]);
                    // 用键注销值
                    unset($data[$v]);
                }
                $error = Cache::set('scv_'.$this->uid.'_'.$filename,$error);
            }
            // -------------------批量新增数据
            $success = $model->limit(100)->insertAll($data);
            // 返回参数
            $return['status'] = ReturnCode::SUCCESS;
            $return['success'] = $success;
            $return['error'] = $errors;
        }else {
            $return['status'] = ReturnCode::ERROR;
        }
        return json($return);
    }


    private function arraySearch($exist,$array,$have)
    {
        foreach ($exist as $key => $value) {
            $k = array_search($value,$array);
            if($k){
                $have[] = $k;
            }
        }
        return $have;
    }
}
