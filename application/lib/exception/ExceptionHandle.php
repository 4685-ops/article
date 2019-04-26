<?php


namespace app\lib\exception;


use think\Exception;
use think\exception\Handle;
use think\Log;
use think\Request;

class ExceptionHandle extends Handle
{

    private $code;
    private $msg;
    private $errorCode;

    /**
     * @function   render   改写原来的系统报错异常
     *
     * @notice   主要在config.php 参数 exception_handle 改成自己重写的异常方法
     *
     * @param Exception $e
     * @return \think\Response|\think\response\Json
     * @author admin
     *
     * @date 2019/4/26 10:03
     */
    public function render(Exception $e)
    {

        if ($e instanceof BaseException) {
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        } else {

            //判断是否是调试模式
            if (config('app_debug')) {
                return parent::render($e);
            }
            $this->code = 500;
            $this->msg = '代码有些错误不想告诉你0.0';
            $this->errorCode = 999;
            // 把系统的一些错误写到日志
            $this->recordErrorLog($e);
        }

        $request = Request::instance();

        $data = [
            'msg' => $this->msg,
            'errorCode' => $this->errorCode,
            'requestUrl' => $request->url()
        ];

        return json($data, $this->code);

    }

    /**
     * @function   recordErrorLog  系统发生错误 把系统的一些错误写到日志当中
     *
     * @param Exception $e
     * @author admin
     *
     * @date 2019/4/26 10:01
     */
    private function recordErrorLog(Exception $e)
    {

        Log::init([
            'type' => 'File',
            'path' => LOG_PATH,
            'level' => ['error']
        ]);

        Log::record($e->getMessage(), 'error');
    }
}