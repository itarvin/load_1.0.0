<?php
namespace app\api\controller;
/**
 * 接口客户处理类
 * @author  itarvin itarvin@163.com
 */
use app\common\model\Member;
use app\util\Tools;
use app\util\ReturnCode;
use think\facade\Request;
use think\Validate;
use think\facade\Cookie;
use \Think\Db;
class Members extends Base
{
    /**
     * 分页数据接口
     * @param page(post)
     * @return json
     */
    public function index()
    {
        if($this->AuthPermission == '200'){
            $member = new Member;
            $where = [];
            $where[] = ['uid', 'EQ', $this->uid];
            $start = Request::param('start', '', 'trim');
            $end = Request::param('end', '', 'trim');
            $key = Request::param('key', '', 'trim');
            $type = Request::param('type', '', 'trim');
            $note = Request::param('note', '', 'trim');
            if($start != '' || $end != '' || ($key != '' && $type != '') || $note != ''){
                //check time
                if ($start && $end) {
                    $where[] = ['newtime', 'between', [$start, $end]];
                }elseif( $start){
                    $where[] = ['newtime', 'GT', $start];
                }elseif ($end) {
                    $where[] = ['newtime', 'LT', $end];
                }
                // check type查询key
                if($type == 'all' || $type == ''){
                    $where[] = ['phone|weixin|qq', 'eq', $key];
                }else if($type == 'qq' || $type == 'phone' || $type == 'weixin'){
                    $where[] = [$type, 'eq', $key];
                }
                if($note != ''){
                    $where[] = ['note', 'LIKE', $note];
                }
            }
            $list = $member->field('id,username,sex,calendar,birthday,qq,phone,weixin,note,newtime,address')->where($where)->order('id desc')->paginate();
            return buildReturn(['status' => ReturnCode::SUCCESS,'info'=>  Tools::errorCode(ReturnCode::SUCCESS),'data' => $list]);
        }else {
            return $this->returnRes($this->AuthPermission, 'true');
        }
    }

    /**
     * 添加客户信息接口
     * @return json
     */
    public function add()
    {
        if($this->AuthPermission == '200'){
            $member = new Member;
            $data = Request::param();
            $data['uid'] = $this->uid;
            $data['newtime'] = date('Y-m-d H:i:s', time());
            $result = $member->store($data);
            return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
        }else {
            return $this->returnRes($this->AuthPermission, 'true');
        }
    }


    /**
     * 修改客户信息接口
     * @return json
     */
    public function edit()
    {
        if($this->AuthPermission == '200'){
            $member = new Member;
            // 接收所有参数
            $data = Request::param();
            $data['uid'] = $this->uid;
            $result = $member->store($data);
            return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
        }else {
            return $this->returnRes($this->AuthPermission, 'true');
        }
    }

    /**
     * 键key获取查询信息接口
     * @param uid
     * @return json
     */
    public function info()
    {
        if($this->AuthPermission == '200'){
            $member = new Member;
            $uid = Request::param('uid', '', 'strip_tags', 'trim');
            $list = $member->field('id,username,sex,calendar,birthday,qq,phone,weixin,note,newtime,address,uid')->find($uid);
            // 验证客户的操作者
            if($list['uid'] == $this->uid){
                unset($list['uid']);
                return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS), 'data' => $list]);
            }else {
                return buildReturn(['status' => ReturnCode::OCCUPIED,'info'=> Tools::errorCode(ReturnCode::OCCUPIED)]);
            }
        }else {
            return $this->returnRes($this->AuthPermission, 'true');
        }
    }
}
