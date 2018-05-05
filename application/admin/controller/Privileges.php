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
        $this->assign('list',$list);
        return $this->fetch('Privileges/index');
    }


    /**
    * 权限添加
    */
    public function add()
    {
        $model = new Privilege;
        $list = $model->getTree();
        $this->assign('list',$list);
        return $this->fetch('Privileges/add');
    }

    /**
    * 添加,更新数据执行方法
    * @return json
    */
    public function handle()
    {
        if(request()->isPost()){
            $input = Request::param();
            $model = new Privilege;
            $result = $model->store($input);
            return buildReturn(['status' => $result['code'], 'info'=> $result['msg']]);
        }
    }


    /**
    * 权限修改
    */
    public function edit()
    {
        // 过滤注入
        $pid = Request::param('pid', '', 'strip_tags');
        $model = new Privilege;
        $list = $model->getTree();
        $data = $model->find($pid);
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
