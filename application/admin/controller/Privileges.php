<?php
namespace app\admin\controller;
/**
 * 应用场景：后台权限类
 * @author  itarvin itarvin@163.com
 */
use app\model\Privilege;
use think\facade\Request;
use app\Util\ReturnCode;
use app\Util\Tools;
class Privileges extends Base
{
    /**
     * 应用场景：权限主页
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
    * 应用场景：权限添加
    * @return json
    */
    public function add()
    {
        $model = new Privilege;

        $list = $model->getTree();

        if(request()->isPost()){

            $result = $model->store(Request::param());

            return buildReturn(['status' => $result['code'], 'info'=> $result['msg']]);
        }

        $this->assign('list',$list);
        return $this->fetch('Privileges/add');
    }


   /**
    * 应用场景：权限修改
    * @return json
    */
    public function edit()
    {
        $model = new Privilege;

        $list = $model->getTree();

        $data = $model->find(Request::param('pid', '', 'strip_tags'));

        if(request()->isPost()){

            $result = $model->store(Request::param());

            return buildReturn(['status' => $result['code'], 'info'=> $result['msg']]);
        }

        $this->assign([
            'data' => $data,
            'list' => $list
        ]);
        return $this->fetch('Privileges/edit');
    }


   /**
    * 应用场景：删除权限
    */
    public function delete()
    {
        if(request()->isPost()){

            $model = new Privilege;

            $result = $model->del(Request::param('pid', '', 'strip_tags'));

            return buildReturn(['status' => $result['code'], 'info'=> $result['msg']]);
        }
    }
}
