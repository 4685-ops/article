<?php

/**
 * 放一些小程序的配置信息
 */
return [
    //小程序app_id
    'app_id' => 'wxd63ac84eadd0e429',

    //小程序app_script
    'app_script' => 'bffda6d06fce0ea7680e864c99b838d0',

    // 微信使用code换取用户openid及session_key的url地址
    'login_url' => "https://api.weixin.qq.com/sns/jscode2session?" .
        "appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",

    // 微信获取access_token的url地址
    'access_token_url' => "https://api.weixin.qq.com/cgi-bin/token?" .
        "grant_type=client_credential&appid=%s&secret=%s",
    'token_salt' => "!@#$%^&*WERTYUIOADFzcxzdawq12312212",
    "token_expire_in" =>7200,
    "img_prefix"=>'http://local.article.com/images'
];
