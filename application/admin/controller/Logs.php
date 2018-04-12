<?php
namespace app\admin\controller;
use app\admin\model\Log;
use app\admin\model\Logact;
use app\util\ReturnCode;
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
        // 预定义type 数组
        $checktype = array('qq,phone,weixin');
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
            // check type
            if (!empty($aid) && !empty($keyword)) {
                $where[] = ['a.act', 'EQ', $aid];
                $where[] = ['a.note', 'LIKE', "%$keyword%"];
            }
            $list = $log->alias('a')
            ->field('a.*,b.users,c.act_name')
            ->join('admin b','a.uid = b.id')
            ->join(['logs_act'=>'c'],'a.act = c.id')
            ->where($where)->order('id desc')->paginate();
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
            $list = $log->alias('a')
            ->field('a.*,b.users,c.act_name')
            ->join('admin b','a.uid = b.id')
            ->join(['logs_act'=>'c'],'a.act = c.id')
            ->where($where)->order('id desc')->paginate();
            foreach($list as $k => $v)
            {
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
