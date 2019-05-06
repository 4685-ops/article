<?php


namespace app\lib\exception;


class UserException extends BaseException
{
    protected $code = 400;
    protected $msg  ="请求的用户id不存在";
    protected $errorCode = 60000;
}