<?php


namespace app\api\validate;

use think\exception\HttpException;
use think\Request;
use think\Validate;
use app\lib\exception\ParameterException;

class BaseValidate extends Validate
{
    /**
     * 检测所有客户端发来的参数是否符合验证类规则
     * 基类定义了很多自定义验证方法
     * 这些自定义验证方法其实，也可以直接调用
     * @return true
     * @throws ParameterException
     */
    public function goCheck()
    {
        $request = Request::instance();
        $params = $request->param();

        if (!$this->check($params)) {

            $exception = new ParameterException([
                'msg' => is_array($this->error) ? implode(";", $this->error) : $this->error
            ]);

            throw $exception;
        }

        return true;
    }

    /**
     * @function   isPositiveInteger    判断值是否是正整数
     *
     * @param $value                    需要判断值
     * @param string $rule
     * @param string $data
     * @param string $field
     * @return bool|string
     * @author admin
     *
     * @date 2019/4/26 9:00
     */
    protected function isPositiveInteger($value, $rule='', $data='', $field='')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        }
        return $field . '必须是正整数';
    }
}