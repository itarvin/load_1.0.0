<?php
namespace app\admin\controller;
use app\common\model\Admin;
use app\common\model\Member;
use app\common\model\Hitcount;
use think\facade\Request;
use app\util\ReturnCode;

class Admins extends Base
{
    // 主页
    public function index()
    {
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
    }


    // 添加页
    public function add()
    {
        return $this->fetch('Admins/add');
    }


    public function create()
    {
        $input = input('post.');
        $user = new Admin;
        // 数据验证
        $result = $this->validate($input, 'app\admin\validate\User');
        if( !$result){
            $data['status'] = ReturnCode::ERROR;
            $data['info'] = $validate->getError();
        }else {
            if ($user->create($input)) {
                $data['status'] = ReturnCode::SUCCESS;
            } else {
                $data['status'] = ReturnCode::ERROR;
                $data['info'] = '新增失败了！';
            }
        }
        return json($data);
    }


    // 更新页
    public function edit()
    {
        $id = Request::param('id', '', 'trim');
        if( $this->superman != 'yes' && $this->uid != $id){
            $this->error('对不起，非法访问！');
        }
        $user = new Admin;
        $data = $user->field('id, users, gender, isow, weixin, phone, qq1, qq2, qq3, qq4')->find($id);
        $this->assign('data', $data);
        return $this->fetch('Admins/edit');
    }


    // 更新方法
    public function update()
    {
        $input = input('post.');
        $user = new Admin;
        if( $this->superman != 'yes' && $this->uid != $input['id']){
            $this->error('对不起，非法访问！');
        }
        $preview = $user->where(array('users'=>$input['users']))->find();
        // 数据验证
        $result = $this->validate($input, 'app\admin\validate\User');
        if( !$result){
            $data['status'] = ReturnCode::ERROR;
            $data['info'] = $validate->getError();
        }else {
            if( $input['pwd'] != $preview['pwd'] && $input['pwd'] != ''){
    	        $input['pwd'] = $input['pwd'];
    	    }else{
    	    	unset($input['pwd']);
    	    }
            if ($user->update($input)) {
                $data['status'] = ReturnCode::SUCCESS;
            } else {
                $data['status'] = ReturnCode::ERROR;
                $data['info'] = '更新失败了！';
            }
        }
        return json($data);
    }


    // 销售的客户页
    public function custom()
    {
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
        $this->assign(array(
            'list' => $list,
            'count'=>$count,
            'uid'  => $this->uid,
            'users'=> $users
        ));
        return $this->fetch('Admins/custom');
    }


    // 离职
    public function dimission()
    {
        $uid = input('uid');
        if( $uid == 1){
            $data['status'] = ReturnCode::AUTH_ERROR;
        }else {
            $re = Admin::where('id', 'EQ', $uid)->update(array('isow' => '1'));
            if( $re){
                $data['status'] = ReturnCode::SUCCESS;
            }else {
                $data['status'] = ReturnCode::ERROR;
            }
        }
        return json($data);
    }



    //修改状态
    public function status()
    {
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
    }


    // 获取点击数
    public function getHit()
    {
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
            $data['status'] = ReturnCode::SUCCESS;
            $data['data']  = $da;
        }else{
            $data['status'] = ReturnCode::ERROR;
        }
        return json($data);
    }
}
