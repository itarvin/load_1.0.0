<?php
namespace app\admin\controller;
/**
 * 应用场景：日志处理类
 * @author  itarvin itarvin@163.com
 */
use app\model\Log;
use app\model\Logact;
use think\facade\Request;
use app\Util\Tools;
use app\Util\ReturnCode;
class Logs extends Base
{
    /**
     * 应用场景：主页显示
     */
    public function index()
    {

        $log = new Log;

        $acts = Logact::field('id, act_name')->select();

        $result = $log->search();

        if(request()->isPost()){
            $result = $log->search(Request::param(), 'true');
        }

        $this->assign([
            'list'  => $result['list'],
            'count' =>$result['count'],
            'uid'   => $this->uid,
            'acts'  => $acts,
        ]);
        return $this->fetch('Logs/index');
    }


    /**
     * 应用场景：提取详细信息
     * @return json
     */
    public function extractDetail()
    {
        if(request()->isPost()){

            $log = new Log;

            $logid = Request::param('logid', '', 'trim');

            $list = $log->where('id',  'EQ',  $logid)->find();

            return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS), 'data' => $list]);
        }
    }
}
