<?php
namespace app\index\controller;
use app\admin\model\Consumer;
use app\util\Tools;
use app\util\ReturnCode;
use think\facade\Request;
use think\Validate;
use think\facade\Cookie;
class Index extends Base
{

    public function index()
    {
        return $this->fetch('Index/index');
    }
    public function memberadd()
    {
        return $this->fetch('Index/memberadd');
    }
    public function memberedit()
    {
        return $this->fetch('Index/memberedit');
    }

    /**
     * 添加客户信息接口
     * @return json
     */
    public function increased()
    {
        $member = new Consumer;
        if($this->AuthPermission == '200'){
            // 接收参数
            $input = Request::param();
            // 数据验证
            $result = $this->validate($input,'app\admin\validate\Member');
            // 添加时做唯一索引处理，局部验证
            $rule = [
                'qq|QQ号' => 'unique:member',
                'phone|电话号' => 'unique:member',
                'weixin|微信号' => 'unique:member',
            ];
            $validate = new Validate($rule);
            $unresult = $validate->check($input);
            // 根据验证返回提示
            if($result !== true){
                $res = array(ReturnCode::ERROR,$result);
            }else if(!$unresult){
                $res = array(ReturnCode::ERROR,$validate->getError());
            }else{
                // 补全数据添加
                $input['uid'] = $this->uid;
                $input['newtime'] = date('Y-m-d H:i:s',time());
                $lastid = $member->insertGetId($input);
                // ------日志处理 ---start
                writelog($lastid,Tools::logactKey('cus_insert'),$this->uid);
                // -------------------end
                if($lastid){
                    $res = array(ReturnCode::SUCCESS,Tools::errorCode(ReturnCode::SUCCESS));
                }else {
                    $res = array(ReturnCode::ERROR,Tools::errorCode(ReturnCode::ERROR),$list);
                }
            }
        }else {
            $res = $this->returnRes($this->AuthPermission,'true');
        }
        return $this->buildReturn($res);
    }


    /**
     * 修改客户信息接口
     * @return json
     */
    public function modify()
    {
        $member = new Consumer;
        if($this->AuthPermission == '200'){
            // 接收所有参数
            $input = Request::param();
            // 获取当前客户键值对应id
            $key = cache($input['key']);
            $preview = $member->field('uid')->find($key);
            // 比对权限
            if($preview['uid'] != $this->uid){
                $res = array(ReturnCode::OCCUPIED,Tools::errorCode(ReturnCode::OCCUPIED),$result);
            }else {
                // 注销键还原原有id
                unset($input['key']);
                $input['id'] = $key;
                // 数据验证
                $result = $this->validate($input,'app\admin\validate\Member');
                if(true !== $result){
                    $res = array(ReturnCode::ERROR,$result);
                }else{
                    $input['uid'] = $this->uid;
                    // ------日志处理 ---start
                    writelog($input['id'],Tools::logactKey('cus_change'),$this->uid);
                    // -------------------end
                    if($member->update($input)){
                        $res = array(ReturnCode::SUCCESS,Tools::errorCode(ReturnCode::SUCCESS));
                    }else {
                        $res = array(ReturnCode::ERROR,Tools::errorCode(ReturnCode::ERROR),$list);
                    }
                }
            }
        }else {
            $res = $this->returnRes($this->AuthPermission,'true');
        }
        return $this->buildReturn($res);
    }

    /**
     * 查询信息接口
     * @return json
     */
    public function getinformation()
    {
        $member = new Consumer;
        $input = Request::param();
        // 验证
        if($this->AuthPermission == '200'){
            if(!empty($input['key']) && !empty($input['type'])){
                // 拼接查询条件
                $where = [];
                if($input['type'] == 'all' || $input['type'] == ''){
                    $where[] = ['phone|weixin|qq','eq',$input['key']];
                }else if($input['type'] == 'qq' || $input['type'] == 'phone' || $input['type'] == 'weixin'){
                    $where[] = [$input['type'],'eq',$input['key']];
                }
                // 查询对象
                $list = $member->where($where)->find();
                // 对象存在且为操作人的客户，返回数据
                if(!empty($list) && $list['uid'] == $this->uid){
                    // 加密键返回
                    $list['key'] = hash('sha512',$list['id'].'itarvin');
                    // 缓存当前键
                    cache($list['key'],$list['id']);
                    $res = array(ReturnCode::SUCCESS,Tools::errorCode(ReturnCode::SUCCESS),$list);
                }else if($list['uid'] != $this->uid) {
                    // 非操作人客户但存在
                    $res = array(ReturnCode::OCCUPIED,Tools::errorCode(ReturnCode::OCCUPIED));
                }else if(empty($list)) {
                    $res = array(ReturnCode::NODATA,Tools::errorCode(ReturnCode::NODATA));
                }
            }else {
                $res = array(ReturnCode::LACKOFPARAM,Tools::errorCode(ReturnCode::LACKOFPARAM));
            }
        }else {
            $res = $this->returnRes($this->AuthPermission,'true');
        }
        return $this->buildReturn($res);
    }

    /**
     * 键key获取查询信息接口
     * @param uid
     * @return json
     */
    public function geteditinfo()
    {
        if($this->AuthPermission == '200'){
            $member = new Consumer;
            $key = Request::param('uid','','strip_tags','trim');
            $uid = cache($key);
            $list = $member->find($uid);
            $list['key'] = $key;
            // 验证客户的操作者
            if($list['uid'] == $this->uid){
                $res = array(ReturnCode::SUCCESS,Tools::errorCode(ReturnCode::SUCCESS),$list);
            }else {
                $res = array(ReturnCode::OCCUPIED,Tools::errorCode(ReturnCode::OCCUPIED));
            }
        }else {
            $res = $this->returnRes($this->AuthPermission,'true');
        }
        return $this->buildReturn($res);
    }

    /**
     * 分页数据接口
     * @param page(post)
     * @return json
     */
    public function list()
    {
        if($this->AuthPermission == '200'){
            // 传值默认取
            $page = Request::param('page','1','strip_tags','trim');
            $member = new Consumer;
            $lists = $member->where(array('uid' => $this->uid))->order('id desc')->page($page,15)->select();
            foreach ($lists as $key => $value) {
                $lists[$key]['key'] = hash('sha512',$value['id'].'itarvin');
                // 缓存当前键
                cache($value['key'],$value['id']);
            }
            $list['data'] = $lists;
            $list['page'] = $page;
            $res = array(ReturnCode::SUCCESS,Tools::errorCode(ReturnCode::SUCCESS),$list);
        }else {
            $res = $this->returnRes($this->AuthPermission,'true');
        }
        return $this->buildReturn($res);
    }
}
