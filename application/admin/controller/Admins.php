<?php
namespace app\admin\controller;
/**
 * 后台用户类
 * @author  itarvin itarvin@163.com
 */
use app\model\Admin;
use app\model\Member;
use app\model\Hitcount;
use think\facade\Request;
use app\Util\ReturnCode;
use app\Util\Tools;
class Admins extends Base
{
    /**
     * 主页
     * @return array
     */
    public function index()
    {
        if($this->superman == 'yes'){
            $user = new Admin;
            $uid = $this->uid;
            $where = [];
            if( request()->isPost()){
                $start = Request::param('start', '', 'trim');
                $end = Request::param('end', '', 'trim');
                $users = Request::param('users', '', 'trim');
                //check time
                if ($start && $end) {
                    $where[] = ['newtime', 'between', [$start, $end]];
                }elseif( $start){
                    $where[] = ['newtime', 'GT', $start];
                }elseif ($end) {
                    $where[] = ['newtime', 'LT', $end];
                }

                if (!empty($users)) {
                    $where[] = ['users', 'LIKE', $users];
                }
            }
            $list = $user->where($where)->order('isow asc')->select();
            $count = $user->count();
            $this->assign([
                'list' => $list,
                'count'=>$count,
                'uid'  => $this->uid
            ]);
            return $this->fetch('Admins/index');
        }else{
            return buildReturn(['status' =>ReturnCode::AUTH_ERROR,'info' => Tools::errorCode(ReturnCode::AUTH_ERROR)]);
        }
    }


    /**
     * 添加页
     */
    public function add()
    {
        if($this->superman == 'yes'){
            return $this->fetch('Admins/add');
        }else{
            return buildReturn(['status' =>ReturnCode::AUTH_ERROR,'info' => Tools::errorCode(ReturnCode::AUTH_ERROR)]);
        }
    }

    /**
     * 新增
     * @return json
     */
    public function create()
    {
        if($this->superman == 'yes'){
            if( request()->isPost()){
                $data = Request::param();
                $user = new Admin;
                $result = $user->store($data);
                return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
            }
        }else {
            return buildReturn(['status' =>ReturnCode::AUTH_ERROR,'info' => Tools::errorCode(ReturnCode::AUTH_ERROR)]);
        }
    }


    /**
     * 更新页
     */
    public function edit()
    {
        if($this->superman == 'yes'){
            $id = Request::param('id', '', 'trim');
            if( $this->superman != 'yes' && $this->uid != $id){
                $this->error('对不起，非法访问！');
            }
            $user = new Admin;
            $data = $user->field('id, users, gender, isow, weixin, phone, qq1, qq2, qq3, qq4')->find($id);
            $this->assign('data', $data);
            return $this->fetch('Admins/edit');
        }else {
            return buildReturn(['status' =>ReturnCode::AUTH_ERROR,'info' => Tools::errorCode(ReturnCode::AUTH_ERROR)]);
        }
    }


    /**
     * 更新方法
     */
    public function update()
    {
        if($this->superman == 'yes'){
            if(request()->isPost()){
                $member = new Admin;
                // 接收所有参数
                $data = Request::param();
                if( $this->superman != 'yes' && $this->uid != $input['id']){
                    $this->error('对不起，非法访问！');
                }
                $data['uid'] = $this->uid;
                $result = $member->store($data);
                return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
            }
        }else {
            return buildReturn(['status' =>ReturnCode::AUTH_ERROR,'info' => Tools::errorCode(ReturnCode::AUTH_ERROR)]);
        }
    }


    /**
     * 销售的客户页
     * @return array
     */
    public function custom()
    {
        if($this->superman == 'yes'){
            // 员工id
            $member = new Member;
            $users = Admin::field('id, users')->order('id asc')->select();
            // 预定义type 数组
            $checktype = array('qq, phone, weixin');
            $where = array();
            if( $this->superman != 'yes'){
                $where[] = ['uid', '=', $this->uid];
            }
            if( request()->isPost()){
                $start = Request::param('start', '', 'trim');
                $end = Request::param('end', '', 'trim');
                $type = Request::param('type', '', 'trim');
                $keyword = Request::param('keyword', '', 'trim');
                $uid = Request::param('uid', '', 'trim');
                //check time
                if ($start && $end) {
                    $where[] = ['newtime', 'between', [$start, $end]];
                }elseif( $start){
                    $where[] = ['newtime', 'GT', $start];
                }elseif ($end) {
                    $where[] = ['newtime', 'LT', $end];
                }
                // check type
                if (!empty($keyword)) {
                    if( $type && in_array($type, $checktype)){
                        $where[] = [$type,  'EQ',  $keyword];
                    }else{
                        $where[] = ['qq|phone|weixin',  'EQ',  $keyword];
                    }
                }
                $where[] = ['uid', 'EQ', $uid];
            }
            $list = $member->where($where)->order('id desc')->paginate();
            $count = $list->total();
            $this->assign([
                'list' => $list,
                'count'=>$count,
                'uid'  => $this->uid,
                'users'=> $users
            ]);
            return $this->fetch('Admins/custom');
        }else {
            return buildReturn(['status' =>ReturnCode::AUTH_ERROR,'info' => Tools::errorCode(ReturnCode::AUTH_ERROR)]);
        }
    }


    /**
     * 离职
     * @return json
     */
    public function dimission()
    {
        if($this->superman == 'yes'){
            $uid = input('uid');
            if( $uid == 1){
                return buildReturn(['status' => ReturnCode::AUTH_ERROR,'info'=> Tools::errorCode(ReturnCode::AUTH_ERROR)]);
            }else {
                $re = Admin::where('id', 'EQ', $uid)->update(array('isow' => '1'));
                if( $re){
                    return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS)]);
                }else {
                    return buildReturn(['status' => ReturnCode::ERROR,'info'=> Tools::errorCode(ReturnCode::ERROR)]);
                }
            }
        }else {
            return buildReturn(['status' =>ReturnCode::AUTH_ERROR,'info' => Tools::errorCode(ReturnCode::AUTH_ERROR)]);
        }
    }


    /**
     * 修改状态
     * @return json
     */
    public function status()
    {
        if($this->superman == 'yes'){
            $uid = Request::param('uid', '', 'trim');
            $user = new Admin;
            $chuqin = $user->field('chuqin')->find($uid);
            switch ($chuqin['chuqin']) {
                case '0':
                    $re = $user->where('id', 'eq', $uid)->update(array('chuqin' => '1'));
                    $chuqin = '1';
                    break;
                case '1':
                    $re = $user->where('id', 'eq', $uid)->update(array('chuqin' => '0'));
                    $chuqin = '0';
                    break;
            }
            if( $re){
                $data['status'] = ReturnCode::SUCCESS;
                $data['chuqin'] = $chuqin;
            }else {
                $data['status'] = ReturnCode::ERROR;
            }
            return json($data);
        }else {
            return buildReturn(['status' =>ReturnCode::AUTH_ERROR,'info' => Tools::errorCode(ReturnCode::AUTH_ERROR)]);
        }
    }


    /**
     * 获取点击数
     * @return json
     */
    public function getHit()
    {
        if($this->superman == 'yes'){
            // 接收时间传参
            $date = input('date');
            // 获取当前所有出勤销售
            $user = new Admin;
            $userIds = $user->field('id')->where('chuqin', 'eq', '1')->select();
            $where = [];
            $end = date('Y-m-d H:i:s', time());
            switch ($date) {
                case 'today':
                    $start = date('Y-m-d 0:0:0', time());
                    $where[] = ['date', 'between', [$start, $end]];
                    break;
                case 'week':
                    $start = date("Y-m-d", strtotime("-1 week"));
                    $where[] = ['date', 'between', [$start, $end]];
                    break;
                case 'halfmonth':
                    $start = date("Y-m-d", strtotime("-15 day"));
                    $where[] = ['date', 'between', [$start, $end]];
                    break;
                case 'month':
                    $start = date("Y-m-d", strtotime("-1 month"));
                    $where[] = ['date', 'between', [$start, $end]];
                    break;
                case 'threemonth':
                    $start = date("Y-m-d", strtotime("-3 month"));
                    $where[] = ['date', 'between', [$start, $end]];
                    break;
                default:
                    $start = date('Y-m-d 0:0:0', time());
                    $where[] = ['date', 'between', [$start, $end]];
                    break;
            }
            foreach ($userIds as $k => $v) {
                $list[$v['id']] = Hitcount::field('pv')->where('uid', 'EQ', $v['id'])->where($where)->select();
            }
            foreach ($list as $key => $value) {
                $pv = 0;
                foreach($value as $k => $v){
                    $pv += $v['pv'];
                }
                $da[$key] = $pv;
            }
            if( $da){
                return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS), 'data' => $da]);
            }else{
                return buildReturn(['status' => ReturnCode::ERROR,'info'=> Tools::errorCode(ReturnCode::ERROR)]);
            }
        }else {
            return buildReturn(['status' =>ReturnCode::AUTH_ERROR,'info' => Tools::errorCode(ReturnCode::AUTH_ERROR)]);
        }
    }
}
