<?php
/**
 * Created by PhpStorm.
 * User: 757208466
 * Date: 2019/5/4
 * Time: 14:56
 */

namespace app\lib\exception;


class ProductException extends BaseException
{
    public $code = 400;
    public $msg  = '指定商品不存在，请检查商品ID';
    public $errorCode = 20000;
}