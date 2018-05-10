<?php
namespace app\admin\controller;
/**
 * 应用场景：网站配置表
 * @author  itarvin itarvin@163.com
 */
use app\model\Setting;
use think\facade\Request;
use app\Util\ReturnCode;
use app\Util\Tools;
class Settings extends Base
{
    /**
     * 应用场景：配置主页
     * @return array
     */
    public function index()
    {
        $model = new Setting;

        $result = $model->search();

        $this->assign([
            'data' => $result['data'],
            'count' => $result['count']
        ]);
        return $this->fetch('Settings/index');
    }


    /**
     * 应用场景：配置添加
     * @return array
     */
    public function add()
    {
        if( request()->isPost()){

            $model = new Setting;

            $result = $model->store(Request::param());

            return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
        }
        return $this->fetch('Settings/add');
    }


    /**
     * 应用场景：配置添加
     * @return array
     */
    public function edit()
    {
        $pid = Request::param('pid', '', 'trim');

        $model = new Setting;

        $data = $model->find($pid);

        if( request()->isPost()){

            $result = $model->store(Request::param());

            return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
        }
        $this->assign('data',$data);
        return $this->fetch('Settings/edit');
    }

    /**
    * 应用场景：删除配置
    * @return json
    */
    public function delete()
    {
        if(request()->isPost()){
            // 过滤注入
            $id = Request::param('pid', '', 'strip_tags');

            $model = new Setting;

            $result = $model->del($id);

            return buildReturn(['status' => $result['code'], 'info'=> $result['msg']]);
        }
    }

    /**
     * 应用场景：批量更新配置全部内容
     * @return [type] [ajax]
     */
    public function updateContent()
    {
        $model = new Setting;

        if( request()->isPost()){

            $tmp = Request::param();

            // 重新拼数组然后循环提交信息
            foreach($tmp['id'] as $k => $v){

                $data[$k]['id'] = $v;
            }
            foreach($tmp['content'] as $k => $v){

                $data[$k]['content'] = $v;
            }
            foreach($tmp['sort_num'] as $k => $v){

                $data[$k]['sort_num'] = $v;
            }
            foreach($data as $v){

                $model->where('id', $v['id'])->update($v);
            }
            return buildReturn(['status' =>ReturnCode::SUCCESS,'info' => Tools::errorCode(ReturnCode::SUCCESS)]);
        }
    }
}
