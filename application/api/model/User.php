<?php


namespace app\api\model;


class User extends BaseModel
{

    public function address(){
        return $this->hasOne('UserAddress','user_id','user_id');
    }

    public static function getOpenidByUserInfo($openid)
    {
        return User::where('openid','=' ,$openid)
            ->find();
    }


}