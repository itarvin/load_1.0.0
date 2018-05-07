<?php
namespace app\admin\controller;
/**
 * 后台用户类
 * @author  itarvin itarvin@163.com
 */
use app\model\Admin;
use app\model\Role;
use app\model\Adminrole;
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
        $user = new Admin;

        $result = $user->search(Request::param());

        $this->assign([
            'list'  => $result['list'],
            'count' => $result['count'],
            'uid'   => $this->uid
        ]);
        return $this->fetch('Admins/index');
    }


    /**
     * 新增
     * @return json
     */
    public function add()
    {
        if( request()->isPost()){

            $user = new Admin;

            $result = $user->store(Request::param());

            return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
        }
        // 拿到角色表的数据
        $roleModel = new Role;

        $roleData = $roleModel->field('id,role_name')->select();

        $this->assign([
            'roleData'   => $roleData,
        ]);
        return $this->fetch('Admins/add');
    }


    /**
     * 更新页
     */
    public function edit()
    {
        $id = Request::param('id', '', 'trim');

        $user = new Admin;
        $roleModel = new Role;
        $arModel = new Adminrole;

        $data = $user->field('id, users, gender, isow, weixin, phone, qq1, qq2, qq3, qq4')->find($id);
        // 拿到角色表的数据

        $roleData = $roleModel->field('id,role_name')->select();

        $roleId = $arModel->field('GROUP_CONCAT(role_id) role_id')
        ->where('admin_id', 'eq', $id)->find();

        $exist = explode(",", $roleId['role_id']);

        if(request()->isPost()){

            // 接收所有参数
            $data = Request::param();

            $data['uid'] = $this->uid;

            $result = $user->store($data);

            return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
        }

        $this->assign([
            'data' =>  $data,
            'roleId' => $exist,
            'roleData'   => $roleData,
        ]);
        return $this->fetch('Admins/edit');
    }


    /**
     * 销售的客户页
     * @return array
     */
    public function custom()
    {
        // 销售id
        $model = new Admin;

        $users = $model->field('id, users')->order('id asc')->select();

        $result = $model->custom();

        if( request()->isPost()){
            $result = $model->custom(Request::param());
        }

        $this->assign([
            'list'  => $result['list'],
            'count' =>$result['count'],
            'page'  =>$result['page'],
            'uid'   => $this->uid,
            'users' => $users
        ]);

        return $this->fetch('Admins/custom');
    }


    /**
     * 离职
     * @return json
     */
    public function dimission()
    {
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
    }


    /**
     * 修改状态
     * @return json
     */
    public function status()
    {
        $uid = Request::param('uid', '', 'trim');

        $user = new Admin;

        $chuqin = $user->field('chuqin')->find($uid);

        switch ($chuqin['chuqin']) {
            case '0':
                $data['status'] = $user->where('id', $uid)->update(['chuqin' => '1']);
                $data['chuqin'] = '1';
                return json($data);
                break;

            case '1':
                $data['status'] = $user->where('id', $uid)->update(['chuqin' => '0']);
                $data['chuqin'] = '0';
                return json($data);
                break;
            }
    }


    /**
     * 获取点击数
     * @return json
     */
    public function getHit()
    {
        // 接收时间传参
        $date = Request::param('date', '', 'tirm');
        // 获取当前所有出勤销售
        $user = new Admin;
        $data = $user->getHitData($date);

        if( $data){

            return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS), 'data' => $data]);
        }else{

            return buildReturn(['status' => ReturnCode::ERROR,'info'=> Tools::errorCode(ReturnCode::ERROR)]);
        }
    }
}
