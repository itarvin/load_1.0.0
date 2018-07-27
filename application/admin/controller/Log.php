<?php
namespace app\admin\controller;
/**
 * 应用场景：日志处理类
 * @author  itarvin itarvin@163.com
 */
use app\model\Log as logModel;
use app\model\Logact;
use think\facade\Request;
use app\Util\Tools;
use app\Util\ReturnCode;
class Log extends Base
{
    /**
     * 应用场景：主页显示
     */
    public function index()
    {
        $log = new logModel;
        $acts = Logact::field('id, act_name')->select();
        $result = $log->search();
        if($this->request->isPost()){
            $result = $log->search($this->request->param(), 'true');
        }
        $this->assign([
            'list'  => $result['list'],
            'count' =>$result['count'],
            'uid'   => $this->uid,
            'acts'  => $acts,
        ]);
        return $this->fetch('');
    }


    /**
     * 应用场景：提取详细信息
     * @return json
     */
    public function extractDetail()
    {
        if($this->request->isPost()){
            $log = new logModel;
            $logid = $this->request->param('logid', '', 'trim');
            $list = $log->where('id',  'EQ',  $logid)->find();
            return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS), 'data' => $list]);
        }
    }
}
