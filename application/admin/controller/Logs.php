<?php
namespace app\admin\controller;
use app\admin\model\Log;
use app\admin\model\Logact;
use app\util\ReturnCode;
use think\facade\Request;
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
                $where[] = ['newtime','between',[$start,$end]];
            }elseif($start){
                $where[] = ['newtime','GT',$start];
            }elseif ($end) {
                $where[] = ['newtime','LT',$end];
            }
            // check type
            if (!empty($keyword)) {
                if($type && in_array($type,$checktype)){
                    $where[] = [$type, 'EQ', $keyword];
                }else{
                    $where[] = ['qq|phone|weixin', 'EQ', $keyword];
                }
            }
        }
        $list = $log->where($where)->order('id desc')->paginate();
        $count = $list->total();
        $this->assign('list',$list);
        $this->assign('count',$count);
        $this->assign('uid',$this->uid);
        $this->assign('acts',$acts);
        return $this->fetch('Logs/index');
    }
}
