<?php


namespace app\lib\exception;


class OrderException extends BaseException
{
    public $code = 400;
    public $msg = '订单中的商品不存在';
    public $errorCode = 80000;
}