<?php


namespace app\api\validate;


class TokenValidate extends BaseValidate
{
    protected $rule = [
        'code' => 'require|isNotEmpty'
    ];

    protected $message = [
        'code' => '没有code还想拿token？做梦哦'
    ];
}