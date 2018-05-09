<?php
namespace app\admin\controller;
/**
 * 订单记录类
 * @author  itarvin itarvin@163.com
 */
use app\model\Member;
use app\model\Record;
use app\Util\ReturnCode;
use app\Util\Tools;
use think\facade\Request;
class Records extends Base
{
    /**
     * 主页
     * @return array
     */
    public function index()
    {
        $record = new Record;

        $uid = $this->uid;

        $where = [];

        // 默认取出当天范围内的客户
        $where[] = ['a.newtime', 'between', [date('Y-m-d', time()), date('Y-m-d H:i:s', time())]];
        // 处理查询
        if(request()->isPost()){
            // 重新处理条件
            $where = [];
            // 检测是否是超管
            if($this->superman != 'yes'){
                $where[] = ['a.uid', 'eq', $this->uid];
            }
            $start = Request::param('start', '', 'trim');
            $end = Request::param('end', '', 'trim');
            $keyword = Request::param('keyword', '', 'trim');
            //check time
            if ($start && $end) {
                $where[] = ['a.newtime', 'between', [$start, $end]];
            }elseif($start){
                $where[] = ['a.newtime', 'GT', $start];
            }elseif ($end) {
                $where[] = ['a.newtime', 'LT', $end];
            }
            //  check keyword
            if (!empty($keyword)) {
                $where[] = ['a.product|a.price',  'LIKE',  "%$keyword%"];
            }
        }
        $list = $record->alias('a')
        ->field('a.*, b.username, c.users')
        ->join('member b', 'b.id = a.khid')
        ->join('admin c', 'c.id = a.uid')
        ->where($where)
        ->order('id desc')->paginate();
        $count = $list->total();
        $this->assign([
            'list'  => $list,
            'count' => $count,
            'uid'   => $uid
        ]);
        return $this->fetch('Records/index');
    }


    /**
     * 添加消费页
     * @return array
     */
    public function add()
    {
        $khid = input('reid');
        // 先检测当前客户是否为当前销售
        $check = Member::find($khid);
        if($check['uid'] != $this->uid){
            $this->error('当前客户您无法操作！');
            exit;
        }

        if(request()->isPost()){
            $input = Request::param();
            $lenght = count($input['product']);
            for($i = 0; $i < $lenght; $i++)
            {
                $da['product'] = $input['product'][$i];
                $da['price'] = $input['price'][$i];
                $da['note'] = $input['note'][$i];
                $da['khid'] = $input['khid'];
                $da['uid'] = $this->uid;
                $da['newtime'] = date('Y-m-d H:i:s', time());
                $data[] = $da;
            }
            // ------日志处理 ---start
            foreach ($data as $key => $value) {
                writelog($value, Tools::logactKey('buy_insert'), $this->uid);
            }
            // -------------------end
            $record = new Record;
            $re = $record->insertAll($data);
            if($re){
                $this->success('添加'.$re.'条订单成功！');
            }
        }

        $check['users'] = $this->name;
        $this->assign([
            'data' => $check,
            'khid' => $khid
        ]);
        return $this->fetch('Records/add');
    }


    /**
     * 删除订单
     * @return json
     */
    public function delete()
    {
        if(request()->isPost()){
            $id = input('post.deleid');
            $record = new Record;
            // 先判断当前是删除数据是否为当前用户的订单。
            $have = $record->where('id', $id)->find();
            if($have['uid']  != $this->uid){
                return buildReturn(['status' => ReturnCode::ERROR,'info'=> '当前订单您不能操作！']);
            }else {
                writelog($id, Tools::logactKey('buy_delete'), $this->uid);
                $re = $record->where('id', $id)->delete();
                if($re){
                    return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS)]);
                }
            }
        }
    }
}
