<?php


namespace app\api\service;

use app\api\model\User;
use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\ParameterException;
use app\lib\exception\WeChatException;
use think\Exception;
use think\Request;
use app\lib\exception\TokenException;

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
        $wxResult['token_expire_in'] = config('wx.token_expire_in');
        $wxResult['currentTime'] = time();

        $online = RedisHash::instance()->setHashKey("loginToken:writeToken");

        $result = $online->set($key, serialize($wxResult));

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
        $cacheValue['scope'] = ScopeEnum::User;
        return $cacheValue;
    }

    public function addUserInfo($openid)
    {
        $data['user_id'] = md5($openid . rand(1, 98765123));

        $data['openid'] = $openid;

        $user = User::create($data);

        return $data['user_id'];
    }

    /**
     * @function   getVerifyToken   这个方法既可以用来检测token是否正确 也可以获取用户信息
     *
     * 调用方法 TokenService::getVerifyToken($token); //token 必须传递
     *
     * @param $token
     * @return mixed
     * @throws ParameterException
     * @author admin
     *
     * @date 2019/4/29 11:19
     */
    public static function getVerifyToken($token)
    {
        $online = RedisHash::instance()->setHashKey("loginToken:writeToken");

        $result = $online->get($token);

        //判断传入的token是否存在
        if (empty($result)) {
            throw new ParameterException([
                'msg' => 'token都不对，还想来获取用户信息'
            ]);
        }

        //检查是否过期
        $userInfo = unserialize($result);

        if (time() - $userInfo['currentTime'] >= 7200) {
            throw new ParameterException([
                'msg' => 'token已过期，请重新获取'
            ]);
        }

        //判断一下 uid 和 openid是否存在
        if (empty($userInfo['uid']) || empty($userInfo['openid'])) {
            throw new ParameterException([
                'msg' => 'token已过期，请重新获取'
            ]);
        }

        unset($userInfo['session_key']);
        $userInfo['flag'] = true;

        return $userInfo;
    }

    public static function getUserInfoByVar($param)
    {
        $token = Request::instance()->header('token');

        if (empty($token)) {
            throw new TokenException([
                'msg' => 'token都没有还想操作，做梦呢',
            ]);
        }

        $userInfo = self::getVerifyToken($token);

        return $userInfo[$param];
    }

    public static function getCurrentUidByToken()
    {
        $uid = self::getUserInfoByVar('uid');

        return $uid;
    }

    /**
     * @function   needPrimaryScope 用户和管理员都可以操作的方法
     *
     * @return bool
     * @throws ForbiddenException
     * @throws TokenException
     * @author admin
     *
     * @date 2019/5/7 10:17
     */
    public static function needPrimaryScope()
    {
        $scope = self::getUserInfoByVar('scope');

        if (!empty($scope)) {
            if ($scope >= ScopeEnum::User) {
                return true;
            } else {
                throw new ForbiddenException();
            }
        } else {
            throw new TokenException();
        }

    }

    /**
     * @function   needExclusiveScope   只有用户才能操作东西
     *
     * @return bool
     * @throws ForbiddenException
     * @throws TokenException
     * @author admin
     *
     * @date 2019/5/7 10:17
     */
    public static function needExclusiveScope()
    {

        $scope = self::getUserInfoByVar('scope');

        if (!empty($scope)) {
            if ($scope == ScopeEnum::User) {
                return true;
            } else {
                throw new ForbiddenException();
            }
        } else {
            throw new TokenException();
        }
    }

    public static function isValidOperate($orderUid)
    {
        $loginUid = self::getCurrentUidByToken();

        if (empty($loginUid)) {
            throw new TokenException();
        }

        if ($loginUid != $orderUid) {
            return false;
        }

        return true;

    }
}