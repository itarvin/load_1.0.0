<?php
namespace app\model;
use think\Model;
class Record extends Model
{
    protected $table='record';

    /**
     * 应用场景：搜索
     * @param string $data  提交的数据
     * @param string $isPost  是否post提交
     * @param Boolean $uid   用户标识
     * @return array
     */
    public function search($data = '', $isPost = 'false', $uid = '')
    {
        $where = [];
        // 默认取出当天范围内的客户
        $where[] = ['a.newtime', 'between', [date('Y-m-d', time()), date('Y-m-d H:i:s', time())]];

        if($isPost == 'true'){

            $where = [];
            $start = isset($data['start']) ? $data['start'] : '';
            $end = isset($data['end']) ? $data['end'] : '';
            $keyword = isset($data['keyword']) ? $data['keyword'] : '';

            //check time
            if ($start && $end) {
                $where[] = ['a.newtime', 'between', [$start, $end]];
            }elseif($start){
                $where[] = ['a.newtime', 'GT', $start];
            }elseif ($end) {
                $where[] = ['a.newtime', 'LT', $end];
            }

            //  check keyword
            if (!empty($keyword)) {
                $where[] = ['a.product|a.price',  'LIKE',  "%$keyword%"];
            }
        }
        // 检测是否是超管
        if(!checksuperman($uid)){
            $where[] = ['a.uid', 'eq', $uid];
        }

        $list = $this->alias('a')
        ->field('a.*, b.username, c.users')
        ->join('member b', 'b.id = a.khid')
        ->join('admin c', 'c.id = a.uid')
        ->where($where)
        ->order('id desc')->paginate();

        $count = $list->total();
        
        return $result = [
            'list'  => $list,
            'count' => $count,
        ];
    }
}
