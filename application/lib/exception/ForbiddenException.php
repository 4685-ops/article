<?php


namespace app\lib\exception;


class ForbiddenException extends BaseException
{
    public $code = 404;
    public $msg = '权限不够';
    public $errorCode = 60001;
}