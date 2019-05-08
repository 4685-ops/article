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
    protected function isPositiveInteger($value, $rule = '', $data = '', $field = '')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @function    isNotEmpty   判断某个变量是否为空
     *
     * @param       $value
     * @param string $rule
     * @param string $data
     * @param string $field
     * @return      bool|string
     * @author      admin
     *
     * @date        2019/4/28 13:26
     */
    protected function isNotEmpty($value, $rule = '', $data = '', $field = '')
    {
        if (empty($value)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @function   isMustMobile 判断一个字符串是不是手机号码
     *
     * @param $value
     * @param string $rule
     * @param string $data
     * @param string $field
     * @return bool
     * @author admin
     *
     * @date 2019/5/6 15:04
     */
    protected function isMustMobile($value, $rule = '', $data = '', $field = '')
    {
        if (!preg_match("/(13[0-9]|15[01235789]|17[0-9]|14[0-9]|18[09])(\d|[0-9]){8}$/", $value))
            return false;
        return true;
    }

    /**
     * @function   getDataByRule    得到验证器里面规定的字段数据
     *
     * @param       $data
     * @return      array
     * @throws      ParameterException
     * @author      admin
     *
     * @date 2019/5/6 17:05
     */
    public function getDataByRule($data)
    {
        $returnArray = [];
        if (array_key_exists('uid', $data) || array_key_exists('user_id', $data)) {
            throw new ParameterException([
                'msg' => '参数中包含有非法的参数名user_id或者uid'
            ]);
        } else {
            foreach ($this->rule as $key => $val) {
                $returnArray [$key] = $data[$key];
            }
        }

        return $returnArray;
    }
}