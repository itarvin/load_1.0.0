<?php
namespace app\model;
use think\Model;
use think\Validate;
use app\Util\ReturnCode;
use app\Util\Tools;
use think\facade\Request;
use app\model\Adminrole;
use app\model\Member;
class Admin extends Model
{
    //主键
    protected $pk = 'id';
    protected $table='admin';


    protected $rule =   [
        'users|用户名'     => 'min:2',
        'phone|手机号'     => 'length:11|number|/^1[3-8]{1}[0-9]{9}$/',
        'weixin|微信号'    => 'length:6,20|/^[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}+$/',
        'qq1|QQ1'          => 'number|length:6,10',
        'qq2|QQ2'          => 'number|length:6,10',
        'qq3|QQ3'          => 'number|length:6,10',
        'qq4|QQ4'          => 'number|length:6,10',
    ];
    protected $message  =   [
        'users.min'                                 => '用户名最少2个字符',
        'phone.number'                              => '手机号必须是数字',
        'phone.length'                              => '手机号长度在11位',
        'weixin.length'                             => '微信号在6-20位字符',
        'phone./^1[3-8]{1}[0-9]{9}$/'               => '请输入正确的手机号',
        'qq1.number'                                => 'QQ1必须是数字',
        'qq1.length'                                => 'QQ1在6-20位字符',
        'qq2.number'                                => 'QQ2必须是数字',
        'qq2.length'                                => 'QQ2在6-20位字符',
        'qq3.number'                                => 'QQ3必须是数字',
        'qq3.length'                                => 'QQ3在6-20位字符',
        'qq4.number'                                => 'QQ4必须是数字',
        'qq4.length'                                => 'QQ4在6-20位字符',
        'weixin./^[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}+$/'=> '请输入正确的微信号',
    ];

    public function store($data)
    {
        $validate  = Validate::make($this->rule,$this->message);
        $result = $validate->check($data);
        if(!$result) {
            return ['code' => ReturnCode::ERROR,'msg' => $validate->getError()];
        }
        $data = Request::only(['id','users','qq1','qq2','qq3','qq4','pwd','weixin','status']);
        $role_id = Request::only(['role_id']);
        if(isset($data['id'])){
            $preview = $this->where(array('users'=>$data['users']))->find();
            if( $data['pwd'] != $preview['pwd'] && $data['pwd'] != ''){
    	        $data['pwd'] = $data['pwd'];
    	    }else{
    	    	unset($data['pwd']);
    	    }
            if($this->update($data)){
                // 删除当前id所存在的权限
                Adminrole::where('admin_id',$data['id'])->delete();
                // 根据关联表的关系，还需对角色权限表进行赋值
        		foreach ($role_id['role_id'] as $k => $v)
        		{
        			Adminrole::create([
        				'role_id' => $v,
        				'admin_id' => $data['id'],
        			]);
        		}
                return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
            }else {
                return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
            }
        }else{
            if($lastid = $this->insertGetId($data)){
                // 根据关联表的关系，还需对角色权限表进行赋值
        		foreach($role_id['role_id'] as $k => $v)
        		{
        			Rolepri::create([
        				'role_id' => $v,
        				'admin_id' => $lastid
        			]);
        		}
                return ['code' => ReturnCode::SUCCESS,'msg' => Tools::errorCode(ReturnCode::SUCCESS)];
            }else {
                return ['code' => ReturnCode::ERROR,'msg' => Tools::errorCode(ReturnCode::ERROR)];
            }
        }
    }


    public function search($data = '')
    {
        $where = [];
        $start = isset($data['start']) ? $data['start'] : '';
        $end  = isset($data['end ']) ? $data['end '] : '';
        $users = isset($data['users']) ? $data['users'] : '';
        //check time
        if ($start && $end) {
            $where[] = ['newtime', 'between', [$start, $end]];
        }elseif( $start){
            $where[] = ['newtime', 'GT', $start];
        }elseif ($end) {
            $where[] = ['newtime', 'LT', $end];
        }
        if (!empty($users)) {
            $where[] = ['users', 'LIKE', $users];
        }
        $list = $this->where($where)->order('isow asc')->select();
        $count = $list->count();
        return $result = [
            'list' => $list,
            'count'=> $count
        ];
    }



    public function custom($data = '')
    {
        // 预定义type 数组
        $checktype = array('qq, phone, weixin');
        $member = new Member;
        $where = [];
        $start = isset($data['start']) ? $data['start'] : '';
        $end  = isset($data['end ']) ? $data['end '] : '';
        $type = isset($data['type']) ? $data['type'] : '';
        $keyword = isset($data['keyword']) ? $data['keyword'] : '';
        $uid  = isset($data['uid ']) ? $data['uid '] : '';
        //check time
        if ($start && $end) {
            $where[] = ['newtime', 'between', [$start, $end]];
        }elseif( $start){
            $where[] = ['newtime', 'GT', $start];
        }elseif ($end) {
            $where[] = ['newtime', 'LT', $end];
        }
        // check type
        if (!empty($keyword)) {
            if( $type && in_array($type, $checktype)){
                $where[] = [$type,  'EQ',  $keyword];
            }else{
                $where[] = ['qq|phone|weixin',  'EQ',  $keyword];
            }
        }
        if($uid != ''){
            $where[] = ['uid', 'EQ', $uid];
        }
        $list = $member->where($where)->order('id desc')->paginate();
        $page = $list->render();
        $count = $list->count();
        return $result = [
            'list'  => $list,
            'count' => $count,
            'page'  => $page,
        ];
    }


    public function getHitData($date)
    {
        $userIds = $this->field('id')->where('chuqin', 'eq', '1')->select();
        $where = [];
        $end = date('Y-m-d H:i:s', time());
        switch ($date) {
            case 'today':
                $start = date('Y-m-d 0:0:0', time());
                $where[] = ['date', 'between', [$start, $end]];
                break;
            case 'week':
                $start = date("Y-m-d", strtotime("-1 week"));
                $where[] = ['date', 'between', [$start, $end]];
                break;
            case 'halfmonth':
                $start = date("Y-m-d", strtotime("-15 day"));
                $where[] = ['date', 'between', [$start, $end]];
                break;
            case 'month':
                $start = date("Y-m-d", strtotime("-1 month"));
                $where[] = ['date', 'between', [$start, $end]];
                break;
            case 'threemonth':
                $start = date("Y-m-d", strtotime("-3 month"));
                $where[] = ['date', 'between', [$start, $end]];
                break;
            default:
                $start = date('Y-m-d 0:0:0', time());
                $where[] = ['date', 'between', [$start, $end]];
                break;
            }
        foreach ($userIds as $k => $v) {
            $list[$v['id']] = Hitcount::field('pv')->where('uid', 'EQ', $v['id'])->where($where)->select();
        }
        foreach ($list as $key => $value) {
            $pv = 0;
            foreach($value as $k => $v){
                $pv += $v['pv'];
            }
            $da[$key] = $pv;
        }
        return $da;
    }
}
