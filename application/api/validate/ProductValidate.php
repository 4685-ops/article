<?php


namespace app\api\validate;


class ProductValidate extends BaseValidate
{
    protected $rule = [
        'count' => 'isPositiveInteger|between:1,15'
    ];

}