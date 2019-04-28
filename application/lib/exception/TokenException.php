<?php


namespace app\lib\exception;


class TokenException
{
    public $code= 401;
    public $msg = 'Token已过期或无效Token';
    public $errorCode = 10002;

}