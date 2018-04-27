<?php
namespace app\common\model;
use think\Model;
class Record extends Model
{
    protected $table='record';
    public static function init()
    {
        self::event('before_delete', function ($id) {
            // if (1 != $user->status) {
            //     return false;
            // }
            var_dump($id);die;
        });
    }
}
