<?php
namespace app\api\controller;
/**
 * 接口客户处理类
 * @author  itarvin itarvin@163.com
 */
use app\model\Admin;
use app\Util\Tools;
use app\Util\ReturnCode;
use think\facade\Request;
use think\File;
class Admins extends Base
{

    /**
     * 修改页面处理
     * @return json
     */
    public function edit()
    {
        if($this->AuthPermission == '200'){
            if( request()->isPost()){

                $admin = new Admin;
                
                $result = $admin->apiStore(Request::param());

                return buildReturn(['status' => $result['code'],'info'=> $result['msg']]);
            }else {

                return buildReturn(['status' => ReturnCode::LACKOFPARAM,'info'=>  Tools::errorCode(ReturnCode::LACKOFPARAM)]);
            }
        }else {

            return $this->returnRes($this->AuthPermission, 'true');
        }
    }


    /**
     * 键key获取查询信息接口
     * @param uid
     * @return json
     */
    public function info()
    {
        if($this->AuthPermission == '200'){

            if( request()->isPost()){
                $admin = new Admin;

                $list = $admin->field('id,users,phone,qq1,qq2,qq3,qq4,description,qq1name,qq2name,qq3name,qq4name,weixin,wxname')->find($this->uid);
                if($list){

                    return buildReturn(['status' => ReturnCode::SUCCESS,'info'=> Tools::errorCode(ReturnCode::SUCCESS), 'data' => $list]);
                }else {

                    return buildReturn(['status' => ReturnCode::NODATA,'info'=> Tools::errorCode(ReturnCode::NODATA)]);
                }
            }else {

                return buildReturn(['status' => ReturnCode::LACKOFPARAM,'info'=>  Tools::errorCode(ReturnCode::LACKOFPARAM)]);
            }
        }else {

            return $this->returnRes($this->AuthPermission, 'true');
        }
    }

}
