<?php
namespace app\model;
use think\Model;
use think\Validate;
use app\Util\ReturnCode;
use app\Util\Tools;
use think\facade\Request;
use app\model\Rolepri;
class Privilege extends Model
{
    //主键
    protected $pk = 'id';
    protected $table='privilege';

    protected $rule = [
        'parent_id|上级ID'          => 'require|number',
        'pri_name|权限名称'         => 'require',
        'module_name|模块名称'      => 'require|alphaDash',
        'controller_name|控制器名称'=> 'alphaDash',
        'action_name|方法名称'      => 'alphaDash',
    ];
    protected $msg = [
        'parent_id.require'                         => '上级ID必须存在',
        'parent_id.number'                          => '上级ID必须是数字',
        'pri_name.require'                          => '权限名称必须存在',
        'module_name.require'                       => '权限名称必须存在',
        'module_name.alphaDash'                     => '权限名称为字母和数字，下划线_及破折号-',
        'controller_name.alphaDash'                 => '控制器名称为字母和数字，下划线_及破折号-',
        'action_name.alphaDash'                     => '方法名称为字母和数字，下划线_及破折号-',
    ];



    /**
     * 新增，修改数据时的数据验证与处理
     * @param string $data    所有数据
     * @return array
     */
    public function store($data)
    {
        // 先检测当前数据是否存在其他行列
        $id = isset($data['id']) ? $data['id'] : '';
        // 基础数据验证
        $validate  = Validate::make($this->rule,$this->msg);
        $result = $validate->check($data);
        // 过滤post数组中的非数据表字段数据
        $data = Request::only(['id','pri_name','module_name','controller_name','action_name','parent_id']);
        if(!$result) {
            return ['code' => ReturnCode::ERROR,'msg' => $validate->getError()];
        }
        if($id != ''){
            if($this->update($data)){
                return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
            }else {
                return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
            }
        }else {
            if($lastid = $this->insertGetId($data)){
                return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
            }else {
                return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
            }
        }
    }

    // 预处理删除
    public function del($id){
        $result = $this->getChildren($id);
        if($result){
            return ['code' => ReturnCode::ERROR,'msg' => '存在下级数据,静止操作！'];
        }else {
            // 这里要处理角色权限，暂留
            if($this->where('id',$id)->delete()){
                return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
            }else {
                return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
            }
        }
    }



    // 拿到子分类
    public function getChildren($id)
    {
        // 取出数据表的所有数据
        $data = $this->select();
        // 回调下一个方法，并传参数
        return $this->_children($data, $id);
    }

    // 私有方法回调子类数据
    private function _children($data, $parent_id=0, $isClear=TRUE)
    {
        // 定义空数组
        static $ret = array();
        // 判定参数是否为真
        if($isClear)
            $ret = array();
        // 循环所有的数据
        foreach ($data as $k => $v)
        {
            // 若父级ID为参数所给的值
            if($v['parent_id'] == $parent_id)
            {
                // 把键给一维数组
                $ret[] = $v['id'];
                // 回调本身，循环取出所有的子类
                $this->_children($data, $v['id'], FALSE);
            }
        }
        // 返回一维数组
        return $ret;
    }


    // 调取树状数组
	public function getTree()
	{
		// 取出当前数据表所有的值
		$data = $this->select();
		// 回调下一个函数数据处理
		return $this->_reSort($data);
	}

	// 以私有方法回调级别排序
	private function _reSort($data, $parent_id=0, $level=0, $isClear=TRUE)
	{
		// 定义一个静态空数组
		static $ret = array();
		// 判断参数是否存在值，若存在，给空数组
		if($isClear)
			$ret = array();
		// 循环输出所有数据的键值
		foreach ($data as $k => $v)
		{
			// 判断每个数组的父类ID 是否等于当前的参数值 0
			if($v['parent_id'] == $parent_id)
			{
				// 存在则存入级别ID值
				$v['level'] = $level;
				// 该数组给定义的空数组
				$ret[] = $v;
				// 回调本身，循环取出所有的递归数据
				$this->_reSort($data, $v['id'], $level+1, FALSE);
			}
		}
		// 返回数组
		return $ret;
	}

	/**
	 * 检查当前管理员是否有权限访问这个页面,
     * @return int
	 */
	public function checkPri()
	{
		// 获取当前管理员正要访问的模型名称、控制器名称、方法名称
		// tP中正带三个常量
		//MODULE_NAME , CONTROLLER_NAME , ACTION_NAME
		$adminId = json_decode(base64_decode(session('uid'),  true), true);
		// 如果是超级管理员直接返回 TRUE
		if(checksuperman($adminId))
			return TRUE;
		// 实例化模型
		$arModel = new Adminrole;
		// 连贯操作并且联表查询数据
		$has = $arModel->alias('a')
        ->join('role_pri b', 'b.role_id = a.role_id')
        ->join('privilege c', 'c.id = b.pri_id')
		->where(array(
			'a.admin_id' => array('eq', $adminId),
			'c.module_name' => array('eq', MODULE_NAME),
			'c.controller_name' => array('eq', CONTROLLER_NAME),
			'c.action_name' => array('eq', ACTION_NAME),
		))->count();
		// 返回值，在继承Base中做rbac使用
		return ($has > 0);
	}

	/**
	 * 获取当前管理员所拥有的前两级的权限
	 * @return array
	 */
	public function getBtns()
	{
		/*************** 先取出当前管理员所拥有的所有的权限 ****************/
		// 从存储的SESSION中获取当前管理员的ID
		$adminId = json_decode(base64_decode(session('uid'),  true), true);
		// 判断是否为超级管理员
		if(checksuperman($adminId))
		{
			// 如果是，则吧所有的权限给超级管理员
			$priData = $this->select();
		}else{
            // 否则，根据所给的角色给相应的权限
			// 取出当前管理员所在角色 所拥有的权限
			$arModel = new Adminrole;
			$priData = $arModel->alias('a')
			->field('DISTINCT c.id,c.pri_name,c.module_name,c.controller_name,c.action_name,c.parent_id')
            ->join('role_pri b', 'b.role_id = a.role_id')
            ->join('privilege c', 'c.id = b.pri_id')
			->where(array(
				'a.admin_id' => array('eq', $adminId),
			))->select();
		}
		/*************** 从所有的权限中挑出前两级的 **********************/
		$btns = [];
		foreach ($priData as $k => $v){
			if($v['parent_id'] == 0){

				foreach($priData as $k1 => $v1){

					if($v1['parent_id'] == $v['id']){

						// $v[$][] = $v1;
                        var_dump($v);die;

					}

				}
				$btns[] = $v;
			}
		}
		return $btns;
	}
}