<?php
namespace app\admin\controller;
/**
 * 后台权限类
 * @author  itarvin itarvin@163.com
 */
use app\model\Privilege;
use think\facade\Request;
use app\Util\ReturnCode;
use app\Util\Tools;
class Privileges extends Base
{
    /**
     * 权限主页
     * @return array
     */
    public function index()
    {
        $model = new Privilege;
        $list = $model->getTree();
        $count = $model->count();
        $this->assign([
            'list' => $list,
            'count' => $count
        ]);
        return $this->fetch('Privileges/index');
    }


    /**
    * 权限添加
    * @return json
    */
    public function add()
    {
        $model = new Privilege;
        $list = $model->getTree();
        if(request()->isPost()){
            $input = Request::param();
            $result = $model->store($input);
            return buildReturn(['status' => $result['code'], 'info'=> $result['msg']]);
        }
        $this->assign('list',$list);
        return $this->fetch('Privileges/add');
    }


    /**
    * 权限修改
    * @return json
    */
    public function edit()
    {
        // 过滤注入
        $pid = Request::param('pid', '', 'strip_tags');
        $model = new Privilege;
        $list = $model->getTree();
        $data = $model->find($pid);
        if(request()->isPost()){
            $input = Request::param();
            $result = $model->store($input);
            return buildReturn(['status' => $result['code'], 'info'=> $result['msg']]);
        }
        $this->assign([
            'data' => $data,
            'list' => $list
        ]);
        return $this->fetch('Privileges/edit');
    }


    /**
    * 删除权限
    */
    public function delete()
    {
        if(request()->isPost()){
            // 过滤注入
            $id = Request::param('pid', '', 'strip_tags');
            $model = new Privilege;
            $result = $model->del($id);
            return buildReturn(['status' => $result['code'], 'info'=> $result['msg']]);
        }
    }
}
