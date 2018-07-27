<?php
namespace app\admin\controller;
/**
 * 应用场景：后台用户类
 * @author  itarvin itarvin@163.com
 */
use app\model\Admin as adminModel;
use app\model\Role;
use app\model\Adminrole;
use app\Util\ReturnCode;
use app\Util\Tools;
class Admin extends Base
{
    /**
     * 应用场景：主页
     * @return array
     */
    public function index()
    {
        $user = new adminModel;
        $result = $user->search($this->request->param());
        $this->assign([
            'list'  => $result['list'],
            'count' => $result['count'],
            'uid'   => $this->uid
        ]);
        return $this->fetch('');
    }

    /**
     * 应用场景：新增
     * @return json
     */
    public function add()
    {
        if( $this->request->isPost()){
            $user = new adminModel;
            $result = $user->store($this->request->param());
            if($result['code'] == 1){
                $this->success($result['msg']);
            }else {
                $this->error($result['msg']);
            }

            // return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
        }
        // 拿到角色表的数据
        $roleModel = new Role;
        $roleData = $roleModel->field('id,role_name')->where('role_status','0')->select();
        $this->assign([
            'roleData'   => $roleData,
        ]);
        return $this->fetch('');
    }

    /**
     * 应用场景：更新页
     */
    public function edit()
    {
        $id = $this->request->param('id', '', 'trim');
        $user = new adminModel;
        $roleModel = new Role;
        $arModel = new Adminrole;
        $data = $user->field('id, users, gender, isow, weixin, phone, qq1, qq2, qq3, qq4, bg')->find($id);
        // 拿到角色表的数据
        $roleData = $roleModel->field('id,role_name')->where('role_status','0')->select();
        $roleId = $arModel->field('GROUP_CONCAT(role_id) role_id')
        ->where('admin_id', 'eq', $id)->find();
        $exist = explode(",", $roleId['role_id']);
        // 拼接二维码路径
        $data['qrcode'] = Tools::getQrcode($id);
        if( $this->request->isPost()){
            // 接收所有参数
            $input = $this->request->param();
            $input['uid'] = $this->uid;
            $result = $user->store($input);
            if($result['code'] == 1){
                $this->success($result['msg']);
            }else {
                $this->error($result['msg']);
            }
            // return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
        }
        $this->assign([
            'data' =>  $data,
            'roleId' => $exist,
            'roleData'   => $roleData,
        ]);
        return $this->fetch('');
    }

    /**
     * 应用场景：销售的客户页
     * @return array
     */
    public function custom()
    {
        // 销售id
        $model = new adminModel;
        $users = $model->field('id, users')->order('id asc')->select();
        $result = $model->custom($this->request->param());
        if( $this->request->isPost()){
            $result = $model->custom($this->request->param());
        }
        $this->assign([
            'list'  => $result['list'],
            'count' => $result['count'],
            'page'  => $result['page'],
            'uid'   => $this->uid,
            'users' => $users
        ]);
        return $this->fetch('');
    }

    /**
     * 应用场景：离职
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
     * 应用场景：修改状态
     * @return json
     */
    public function status()
    {
        $uid = $this->request->param('uid', '', 'trim');
        $user = new adminModel;
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
     * 应用场景：获取点击数
     * @return json
     */
    public function getHit()
    {
        // 接收时间传参
        $date = $this->request->param('date', '', 'tirm');
        // 获取当前所有出勤销售
        $user = new adminModel;
        $data = $user->getHitData($date);
        if( $data){
            return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS), 'data' => $data]);
        }else{
            return buildReturn(['status' => ReturnCode::ERROR,'info'=> Tools::errorCode(ReturnCode::ERROR)]);
        }
    }
}
