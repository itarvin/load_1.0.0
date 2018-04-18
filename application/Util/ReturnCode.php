<?php
/**
 * 错误码统一维护
 * @since   2018/03/24 创建
 * @author  iatrvin <iatrvin@163.com>
 */

namespace app\util;

class ReturnCode {

    const SUCCESS = 1;
    const ERROR = 0;
    const NODATA = -1;
    const AUTH_ERROR = -14;
    const UNKNOWN = -998;
    const EXCEPTION = -999;
    const VERIFICATIONFAILURE = -2;

    static public function getConstants() {
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

}
