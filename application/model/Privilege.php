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
		->where([
			['a.admin_id', 'eq', $adminId],
			['c.module_name', 'eq', request()->module()],
			['c.controller_name', 'eq', request()->controller()],
			['c.action_name', 'eq', request()->action()],
		])->count();
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
        $ar = [];
        foreach ($priData as $key => $value) {
            $ar['id']                = $value['id'];
            $ar['pri_name']          = $value['pri_name'];
            $ar['module_name']       = $value['module_name'];
            $ar['controller_name']   = $value['controller_name'];
            $ar['action_name']       = $value['action_name'];
            $ar['parent_id']         = $value['parent_id'];
            $pd[] = $ar;
        }
		foreach ($pd as $k => $v){
            /*判断循环的值得父级ID，存在，就继续向下挖递归，不存在该值，跳过这条数据*/
			if($v['parent_id'] == 0)
			{
				// 再找这个顶的子级，再次开始循环整个数据
				foreach ($priData as $k1 => $v1)
				{
					// 判定第二次循环的数组的父级ID是否为上一层的值ID
					if($v1['parent_id'] == $v['id'])
					{
						// 存在，存二维数组
						$v['children'][] = $v1;
					}
				}
				// 存一维数组
				$btns[] = $v;
			}
		}
		return $btns;
	}
}
