<?php
namespace app\model;
use think\Model;
use think\Validate;
use app\Util\ReturnCode;
use app\Util\Tools;
use app\model\Rolepri;
use think\facade\Request;
class Role extends Model
{
    //主键
    protected $pk = 'id';
    protected $table='role';


    protected $rule = [
        'role_name|角色名称'         => 'require|min:2|unique:role',
        'role_status|角色状态'       => 'require|number',
        'role_desc|角色描述'         => 'min:2',
    ];
    protected $msg = [
        'role_name.require'                        => '角色名称必须存在',
        'role_name.min:2'                          => '角色名称至少2个字符',
        'role_status.require'                      => '角色状态必须存在',
        'role_status.number'                       => '角色状态必须是数值',
        'role_desc.min:2'                          => '至少2个字符',
    ];

    /**
     * 应用场景：新增，修改数据时的数据验证与处理
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
        $data = Request::only(['id','role_name','role_status','role_desc']);

        $priId = Request::only(['pri_id']);

        if(!$result) {

            return ['code' => ReturnCode::ERROR,'msg' => $validate->getError()];
        }
        if($id != ''){

            if($this->update($data)){

                // 删除当前id所存在的权限
                Rolepri::where('role_id',$data['id'])->delete();

                // 根据关联表的关系，还需对角色权限表进行赋值
        		foreach ($priId['pri_id'] as $k => $v){

        			Rolepri::create([
        				'pri_id' => $v,
        				'role_id' => $data['id'],
        			]);
        		}
                return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
            }else {

                return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
            }
        }else {

            if($lastid = $this->insertGetId($data)){

                // 根据关联表的关系，还需对角色权限表进行赋值
        		foreach ($priId['pri_id'] as $k => $v){
        			Rolepri::create([
        				'pri_id' => $v,
        				'role_id' => $lastid,
        			]);
        		}
                return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
            }else {

                return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
            }
        }
    }


    /**
     * 应用场景：自定义搜索信息
     * @param array $data 提价的数据集
     * @return array
     */
     public function search($data = ''){

         $where = [];
         $role_status = isset($data['role_status']) ? $data['role_status'] : '';

         $role_name = isset($data['role_name']) ? $data['role_name'] : '';

         if($role_status != ''){
             $where[] = ['a.role_status', 'eq' ,$role_status];
         }
         if($role_name != ''){
             $where[] = ['a.role_name', 'LIKE' ,"%$role_name%"];
         }

         $list = $this->alias('a')
         ->field('a.*,GROUP_CONCAT(c.pri_name) pri_name')
         ->join('role_pri b','a.id = b.role_id')
         ->join('privilege c','b.pri_id = c.id')
         ->group('a.id')
         ->where($where)->select();

         $count = $list->count();

         return $retult = [
             'data' => $list,
             'count' => $count
         ];
     }

     /**
      * 应用场景：预处理删除
      * @param int $id 主键id
      * @return array
      */
     public function del($id)
     {
         // 删除角色携带的权限
         Rolepri::where('role_id', $id)->delete();
         $result = $this->where('id',$id)->delete();

         if($result){

             return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
         }else {
             
             return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
         }
     }
}
