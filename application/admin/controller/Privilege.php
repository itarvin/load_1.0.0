<?php
namespace app\admin\controller;
/**
 * 应用场景：后台权限类
 * @author  itarvin itarvin@163.com
 */
use app\model\Privilege as priModel;
use app\Util\ReturnCode;
use app\Util\Tools;
class Privilege extends Base
{
    /**
     * 应用场景：权限主页
     * @return array
     */
    public function index()
    {
        $model = new priModel;
        $list = $model->getTree();
        $count = $model->count();
        $this->assign([
            'list' => $list,
            'count' => $count
        ]);
        return $this->fetch('');
    }

   /**
    * 应用场景：权限添加
    * @return json
    */
    public function add()
    {
        $model = new priModel;
        $list = $model->getTree();
        if($this->request->isPost()){
            $result = $model->store($this->request->param());
            return buildReturn(['status' => $result['code'], 'info'=> $result['msg']]);
        }
        $this->assign('list',$list);
        return $this->fetch('');
    }


   /**
    * 应用场景：权限修改
    * @return json
    */
    public function edit()
    {
        $model = new priModel;
        $list = $model->getTree();
        $data = $model->find($this->request->param('pid', '', 'strip_tags'));
        if($this->request->isPost()){
            $result = $model->store($this->request->param());
            return buildReturn(['status' => $result['code'], 'info'=> $result['msg']]);
        }
        $this->assign([
            'data' => $data,
            'list' => $list
        ]);
        return $this->fetch('');
    }

   /**
    * 应用场景：删除权限
    */
    public function delete()
    {
        if($this->request->isPost()){
            $model = new priModel;
            $result = $model->del($this->request->param('pid', '', 'strip_tags'));
            return buildReturn(['status' => $result['code'], 'info'=> $result['msg']]);
        }
    }
}
