<?php
namespace app\admin\controller;
use app\admin\model\Log;
use app\admin\model\Logact;
use think\facade\Request;
use app\util\Tools;
class Logs extends Base{
    /**
     * 主页显示
     */
    public function index()
    {
        $log = new Log;
        $acts = Logact::field('id,act_name')->select();
        // 获取当前管理员的客户会员
        $where = array();
        if(request()->isPost()){
            $start = Request::param('start','','trim');
            $end = Request::param('end','','trim');
            $aid = Request::param('aid','','trim');
            $keyword = Request::param('keyword','','trim');
            //check time
            if ($start && $end) {
                $where[] = ['a.newtime','between',[$start,$end]];
            }elseif($start){
                $where[] = ['a.newtime','GT',$start];
            }elseif ($end) {
                $where[] = ['a.newtime','LT',$end];
            }
            // check keyword
            if (!empty($aid) && !empty($keyword)) {
                $where[] = ['a.note', 'LIKE', "%$keyword%"];
            }
            $where[] = ['a.act', 'EQ', $aid];
        }
        // 提取对应数据
        $list = $log->alias('a')
        ->field('a.*,b.users,c.act_name')
        ->join('admin b','a.uid = b.id')
        ->join(['logs_act'=>'c'],'a.act = c.id')
        ->where($where)->order('id desc')->paginate();
        // 是否提交了关键字
        if(!empty($keyword)){
            $lenght = mb_strlen($keyword,'utf8');
            foreach ($list as $key => $value) {
                $have = strpos($value['note'],$keyword);
                if($have){
                    $list[$key]['position'] = $have;
                    $list[$key]['lenght'] = $lenght;
                    $list[$key]['k'] = '1';
                }
            }
        }else {
            foreach($list as $k => $v){
                $json = json_decode($v['note']);
                if(json_last_error() == JSON_ERROR_NONE){
                    $note = '';
                    foreach ($json as $key => $value) {
                        $note .= Tools::keywordReplace($key).'：'.$value."，";
                    }
                    $list[$k]['note'] = $note;
                }
                $list[$k]['k'] = '0';
            }
        }
        $count = $list->total();
        $this->assign(array(
            'list' => $list,
            'count'=>$count,
            'uid'  => $this->uid,
            'acts' => $acts,
        ));
        return $this->fetch('Logs/index');
    }
}
