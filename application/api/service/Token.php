<?php


namespace app\api\service;


class Token
{
    // 生成令牌
    public static function generateToken()
    {
        $randChar = getRandChar(32);
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        $tokenSalt = config('wx.token_salt');
        return md5($randChar . $timestamp . $tokenSalt);
    }

}