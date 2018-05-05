<?php
namespace app\model;
use think\Model;
use think\Validate;
use app\Util\ReturnCode;
use app\Util\Tools;
class Role extends Model
{
    //主键
    protected $pk = 'id';
    protected $table='role';
}
