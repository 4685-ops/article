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


/**
 *  banner 相关的接口
 */
Route::get('api/:version/banner/getBannerItemInfoByBannerId/:id', 'api/v1.Banner/getBannerItemInfoByBannerId');

Route::get('api/:version/token/getToken/:code', 'api/v1.Token/getToken');
Route::post('api/:version/token/getVerifyToken', 'api/v1.Token/getVerifyToken');

/**
 * 主题相关的接口
 */
Route::get('api/:version/theme/getThemeByIds', 'api/v1.Theme/getThemeByIds');
Route::get('api/:version/theme/getThemeInfoByThemeId/:id', 'api/v1.Theme/getThemeInfoByThemeId');

/**
 *  商品相关的接口
 */

Route::get('api/:version/product/getRecentGoods', 'api/v1.Product/getRecentGoods');
Route::get('api/:version/product/getCategoryDataByCategoryId/:id', 'api/v1.Product/getCategoryDataByCategoryId');
Route::get('api/:version/product/getProductInfoByProductId', 'api/v1.Product/getProductInfoByProductId');


/**
 * 分类相关的接口
 */
Route::get('api/:version/category/getAllCategory', 'api/v1.Category/getAllCategory');


/**
 * 地址相关的接口
 */

Route::post('api/:version/address/createUserAddress', 'api/v1.Address/createUserAddress');

/**
 * 订单相关的接口
 */
Route::post('api/:version/order/placeOrder', 'api/v1.Order/placeOrder');
Route::post('api/:version/order/getSummaryByUser', 'api/v1.Order/getSummaryByUser');
Route::post('api/:version/order/getDetail', 'api/v1.Order/getDetail');

Route::post('api/:version/pay/getPreOrder', 'api/v1.Pay/getPreOrder');
Route::post('api/:version/pay/notify', 'api/v1.Pay/receiveNotify');

