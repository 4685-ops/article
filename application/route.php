<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

// 定义GET请求路由规则
Route::get('banner/getBannerItemInfoByBannerId/:id','api/v1.Banner/getBannerItemInfoByBannerId');
Route::get('token/getToken/:code','api/v1.Token/getToken');
Route::post('token/getVerifyToken','api/v1.Token/getVerifyToken');


