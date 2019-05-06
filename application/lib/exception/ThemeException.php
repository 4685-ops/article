<?php
/**
 * Created by PhpStorm.
 * User: 757208466
 * Date: 2019/5/3
 * Time: 17:24
 */

namespace app\lib\exception;


class ThemeException extends BaseException
{
    public $code = 400;
    public $msg = '指定主题不存在，请检查主题ID';
    public $errorCode = 30000;
}