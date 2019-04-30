<?php


namespace app\api\validate;


class TokenEmptyValidate extends BaseValidate
{
    protected $rule = [
        'token' => 'require|isNotEmpty'
    ];

    protected $message = [
        'token' => '没有token还想拿用户信息？做梦哦'
    ];
}