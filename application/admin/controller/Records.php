<?php
namespace app\admin\controller;
use app\admin\model\Consumer;
use app\admin\model\Record;
use think\Validate;
use app\util\ReturnCode;
use app\util\Tools;
class Records extends Base
{
    // 主页
    public function index()
    {
        $record = new Record;
        $uid = $this->uid;
        $where = array();
        if($this->uid != 1)
        {
            $where[] = ['a.uid','eq',$this->uid];
        }
        if(request()->isPost()){
            $start = Request::param('start','','trim');
            $end = Request::param('end','','trim');
            $keyword = Request::param('keyword','','trim');
            //check time
            if ($start && $end) {
                $where[] = ['newtime','between',[$start,$end]];
            }elseif($start){
                $where[] = ['newtime','GT',$start];
            }elseif ($end) {
                $where[] = ['newtime','LT',$end];
            }

            if (!empty($keyword)) {
                $where[] = ['product|price', 'LIKE', $keyword];
            }
        }
        $list = $record->alias('a')
        ->field('a.*,b.username,c.users')
        ->join('member b','b.id = a.khid')
        ->join('admin c','c.id = a.uid')
        ->where($where)
        ->order('id desc')->paginate();
        $count = $list->total();
        $this->assign('list',$list);
        $this->assign('count',$count);
        $this->assign('uid',$uid);
        return $this->fetch('records/index');
    }


    // 添加消费
    public function addrecord()
    {
        $khid = input('reid');
        // 先检测当前客户是否为当前销售
        $check = Consumer::find($khid);
        if($check['uid'] != $this->uid){
            $this->error('当前客户您无法操作！');
            exit;
        }
        $check['users'] = $this->name;
        $this->assign('data',$check);
        $this->assign('khid',$khid);
        return $this->fetch('Records/addrecord');
    }


    public function record()
    {
        $input = input('post.');
        $lenght = count($input['product']);
        for($i = 0; $i < $lenght;$i++)
        {
            $da['product'] = $input['product'][$i];
            $da['price'] = $input['price'][$i];
            $da['note'] = $input['note'][$i];
            $da['khid'] = $input['khid'];
            $da['uid'] = $this->uid;
            $da['newtime'] = date('Y-m-d H:i:s',time());
            $data[] = $da;
        }
        // ------日志处理 ---start
        foreach ($data as $key => $value) {
            writelog($value,Tools::logactKey('buy_insert'),$this->uid);
        }
        // -------------------end
        $record = new Record;
        $re = $record->insertAll($data);
        if($re){
            $this->success('添加'.$re.'条订单成功！','Admin/index');
        }
    }



    //删除订单

    public function delete()
    {
        $id = input('post.deleid');
        $record = new Record;
        // 先判断当前是删除数据是否为当前用户的订单。
        $have = $record->where('id',$id)->find();
        if($have['uid']  != $this->uid){
            $data['status'] = ReturnCode::ERROR;
            $data['msg'] = '当前订单您不能操作！';
        }else {
            writelog($id,Tools::logactKey('buy_delete'),$this->uid);
            $re = $record->where('id',$id)->delete();
            if($re){
                $data['status'] = ReturnCode::SUCCESS;;
            }
        }
        return json($data);
    }
}
