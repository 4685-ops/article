<?php
/**
 * Created by PhpStorm.
 * User: 757208466
 * Date: 2019/5/3
 * Time: 17:14
 */

namespace app\api\validate;


class IDCollection extends BaseValidate
{
    protected $rule = [
        'ids' => 'require|checkIds'
    ];

    protected $message = [
        'ids' => 'ids参数必须为以逗号分隔的多个正整数,仔细看文档啊'
    ];

    public function checkIds($value)
    {
        $data = explode(',', $value);

        if (empty($data)) {
            return false;
        }

        foreach ($data as $val) {
            if (!$this->isPositiveInteger($val)) {
                return false;
            }
        }

        return true;
    }
}