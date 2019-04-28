<?php


namespace app\api\model;


class User extends BaseModel
{

    public static function getOpenidByUserInfo($openid)
    {
        return User::where('openid','=' ,$openid)
            ->find();
    }


}