<?php
namespace app\model;
use think\Model;
use think\Validate;
use app\Util\ReturnCode;
use app\Util\Tools;
use think\facade\Request;
class Setting extends Model
{
    //主键
    protected $pk = 'id';
    protected $name='setting';

    protected $rule =   [
        'title|配置标题'          => 'min:2|unique:setting',
        'name|配置名称'           => 'require|alphaDash|unique:setting',
        'field_type|类型'        => 'require',
        'sort_num|排序'          => 'number|between:0,300',
    ];
    protected $message  =   [
        'title.min'                                 => '配置标题最少2个字符',
        'name.require'                              => '配置名称必须存在',
        'name.alphaDash'                            => '配置名称只能是字母和数字，下划线_及破折号-',
        'field_type.require'                        => '类型必须存在',
        'sort_num.number'                           => '排序只能为数字',
        'sort_num.between'                          => '排序值在0-300内',
    ];


    /**
     * 应用场景：新增，修改数据时的数据验证与处理
     * @param string $data    所有数据
     * @return array
     */
    public function store($data)
    {
        $validate  = Validate::make($this->rule,$this->message);

        $result = $validate->check($data);

        if(!$result) {

            return ['code' => ReturnCode::ERROR,'msg' => $validate->getError()];
        }
        $data = Request::only(['id','title','name','content','sort_num','tips','field_type','field_value','is_system']);

        if(isset($data['id'])){

            if($this->update($data)){

                return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
            }else {

                return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
            }
        }else{

            if($lastid = $this->insertGetId($data)){

                return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
            }else {

                return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
            }
        }
    }

    /**
     * 应用场景：主页显示搜索
     * @return array
     */
    public function search()
    {
        $data = $this->order('sort_num  desc')->select();

        $count = $data->count();
        // 遍历制作数据分析
        foreach ($data as $k => $v) {

            switch ($v['field_type']) {
                case 'input':
                    $data[$k]['html'] = '<input type="text" class="layui-input" name="content[]" value="'.$v['content'].'">';
                    break;

                case 'textarea':
                    $data[$k]['html'] = '<textarea type="text" class="layui-textarea"  name="content[]">'.$v['content'].'</textarea>';
                    break;

                case 'radio':
                    //1|开启,0|关闭
                    $arr = explode('，',$v['field_value']);
                    $str = '';
                    foreach($arr as $m => $n){
                        //1|开启
                        $r = explode('|',$n);
                        $c = $v['content'] == $r[0] ? ' checked ' : '';
                        $str .= '<input type="radio" name="content[]" value="'.$r[0].'"'.$c.'>'.$r[1].'　';
                    }
                    $data[$k]['html'] = $str;
                    break;
            }
        }
        return $result = [
            'data' => $data,
            'count' => $count,
        ];
    }

    /**
     * 应用场景：预处理删除
     * @param int $id 主键id
     * @return array
     */
    public function del($id)
    {
        $result = $this->where('id',$id)->delete();

        if($result){

            return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
        }else {

            return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
        }
    }
}
