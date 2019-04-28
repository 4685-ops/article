<?php


namespace app\api\service;


use app\api\model\User;
use app\lib\exception\WeChatException;
use think\Cache;
use think\Exception;

class TokenService
{
    protected $code;
    protected $appId;
    protected $appScript;
    protected $loginUrl;

    public function __construct($code)
    {
        $this->code = $code;
        $this->appId = config('wx.app_id');
        $this->appScript = config('wx.app_script');
        $this->loginUrl = sprintf(config('wx.login_url'), $this->appId, $this->appScript, $this->code);

    }

    public function get()
    {
        $curl = new Curl();

        $result = $curl->asJson(true)->get($this->loginUrl)->response;

        if (empty($result)) {
            throw new Exception('获取session_key及openID时异常，微信内部错误');
        } else {

            if (array_key_exists('errcode', $result)) {
                // 说明有
                $this->getWeChatOpenidError($result);
            } else {
                // 保存数据库 并生成token
                return $this->grantToken($result);
            }
        }
    }

    protected function getWeChatOpenidError($result)
    {

        throw new WeChatException([
            'msg' => $result['errmsg'],
            'errorCode' => $result['errcode']
        ]);
    }


    protected function grantToken($result)
    {
        //根据openid 获取user_id 有不添加 没有添加
        $openid = $result['openid'];

        $userInfo = User::getOpenidByUserInfo($openid);

        if (empty($userInfo)) {
            //add
            $userId = $this->addUserInfo($openid);
        } else {
            $userId = $userInfo->user_id;
        }

        //发布令牌
        $cacheData = $this->prepareCachedValue($result, $userId);

        $token = $this->getSuccessToken($cacheData);

        return $token;
    }

    public function getSuccessToken($wxResult)
    {
        $key = Token::generateToken($wxResult);
        $expire_in = config('wx.token_expire_in');
        $wxResult['token_expire_in'] = $expire_in;

        $value = json_encode($wxResult);

        //写到redis里面
        $result = Cache::store('redis')->set($key, $value);

        if (!$result) {
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }
        return $key;
    }

    public function prepareCachedValue($result, $userId)
    {
        $cacheValue = $result;
        $cacheValue['uid'] = $userId;

        return $cacheValue;
    }

    public function addUserInfo($openid)
    {
        $data['user_id'] = md5($openid . rand(1, 98765123));

        $data['openid'] = $openid;

        $user = User::create($data);

        return $data['user_id'];
    }
}