<?php
/**
 * Created by PhpStorm.
 * User: 757208466
 * Date: 2019/5/4
 * Time: 16:27
 */

namespace app\lib\exception;


class CategoryException extends BaseException
{
    public $code = 400;
    public $msg  = '指定分类不存在';
    public $errorCode = 40000;
}