<?php
namespace app\admin\controller;
use app\admin\model\Administrators;
use think\facade\Request;
use think\Validate;
use app\util\ReturnCode;
class Admin extends Base
{
    public function index()
    {
        $user = new Administrators;
        $uid = $this->uid;
        $where = array();
        if(request()->isPost()){
            $start = Request::param('start','','trim');
            $end = Request::param('end','','trim');
            $users = Request::param('users','','trim');
            //check time
            if ($start && $end) {
                $where[] = ['newtime','between',[$start,$end]];
            }elseif($start){
                $where[] = ['newtime','GT',$start];
            }elseif ($end) {
                $where[] = ['newtime','LT',$end];
            }

            if (!empty($users)) {
                $where[] = ['users', 'LIKE', $users];
            }
        }
        $list = $user->where($where)->order('id asc')->paginate();
        $count = $list->count();
        $this->assign('list',$list);
        $this->assign('count',$count);
        $this->assign('uid',$uid);
        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch();
    }


    public function edit()
    {
        $id = Request::param('id','','trim');
        if($this->uid != 1 && $this->uid != $id){
            $this->error('对不起，非法访问！');
        }
        $user = new Administrators;
        $data = $user->field('id,users,weixin,phone,qq1,qq2,qq3,qq4')->find($id);
        $this->assign('data',$data);
        return $this->fetch('admin/edit');
    }


    public function update()
    {
        $input = input('post.');
        $user = new Administrators;
        if($this->uid != 1 && $this->uid != $input['id']){
            $this->error('对不起，非法访问！');
        }
        $preview = $user->where(array('users'=>$input['users']))->find();
        // 数据验证
        $result = $this->validate($input,'app\admin\validate\User');
        if(!$result){
            $data['status'] = ReturnCode::ERROR;
            $data['info'] = $validate->getError();
        }else {
            if($input['pwd'] != $preview['pwd']){
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
}
