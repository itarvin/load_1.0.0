<?php
namespace app\admin\controller;
/**
 * 应用场景：订单记录类
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
     * 应用场景：主页
     * @return array
     */
    public function index()
    {
        $record = new Record;
        $result = $record->search('', 'false', $this->uid);
        // 处理查询
        if(request()->isPost()){
            $result = $record->search(Request::param(), 'true', $this->uid);
        }
        $this->assign([
            'list'  => $result['list'],
            'count' => $result['count'],
            'uid'   => $this->uid
        ]);
        return $this->fetch('Records/index');
    }


    /**
     * 应用场景：添加消费页
     * @return array
     */
    public function add()
    {
        $khid = input('reid');

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

        // 先检测当前客户是否为当前销售
        $check = Member::find($khid);

        if($check['uid'] != $this->uid){
            $this->error('当前客户您无法操作！');
        }
        $check['users'] = $this->name;
        $this->assign([
            'data' => $check,
            'khid' => $khid
        ]);
        return $this->fetch('Records/add');
    }


    /**
     * 应用场景：删除订单
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

                if($record->where('id', $id)->delete()){

                    return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS)]);
                }else {

                    return buildReturn(['status' => ReturnCode::ERROR,'info'=> Tools::errorCode(ReturnCode::ERROR)]);
                }
            }
        }
    }
}
