<?php


namespace app\api\validate;


class AddressValidate extends BaseValidate
{
    public $rule = [
        'name' => 'require|isNotEmpty',
        'mobile' => 'require|isMustMobile',
        'province' => 'require|isNotEmpty',
        'city' => 'require|isNotEmpty',
        'country' => 'require|isNotEmpty',
        'detail' => 'require|isNotEmpty',
    ];
}
