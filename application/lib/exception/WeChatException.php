<?php


namespace app\lib\exception;


class WeChatException extends BaseException
{
    public $code = 400;
    public $message = '微信内部错误';
    public $errorCode = 10002;
}