<?php


namespace app\api\validate;


use app\lib\exception\ParameterException;

class OrderPlaceValidate extends BaseValidate
{
    protected $rule = [
        'products' => 'checkProducts'
    ];
    protected $singRule = [
        'product_id' => 'require|isPositiveInteger',
        'count' => 'require|isPositiveInteger'
    ];

    public function checkProducts($values)
    {
        if (empty($values)) {
            throw new ParameterException([
                'msg' => '商品列表不能为空'
            ]);
        }
        if (is_array($values)) {
            foreach ($values as $key => $val) {
                $this->checkProduct($val);
            }
        }
        return true;
    }

    public function checkProduct($data)
    {
        $baseValidate = new BaseValidate($this->singRule);
        $result = $baseValidate->check($data);
        if (!$result) {
            throw new ParameterException([
                'msg' => '商品列表参数错误'
            ]);
        }
    }
}