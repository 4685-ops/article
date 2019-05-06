<?php
/**
 * Created by PhpStorm.
 * User: 757208466
 * Date: 2019/5/4
 * Time: 14:45
 */

namespace app\api\controller\v1;


use app\api\validate\IDMustBePositiveInt;
use app\api\validate\ProductValidate;
use app\lib\exception\CategoryException;
use app\lib\exception\ProductException;
use think\Controller;
use app\api\model\Product as productModel;

class Product extends Controller
{
    /**
     * @function   getRecentGoods  获取最近的商品
     *
     * @example  http://local.article.com/api/v1/product/getRecentGoods?count=15
     *
     * @param int $count
     * @return string|\think\response\Json
     * @throws ProductException
     * @throws \app\lib\exception\ParameterException
     * @author admin
     *
     * @date 2019/5/4
     */
    public function getRecentGoods($count = 15)
    {
        (new ProductValidate())->goCheck();

        $product = productModel::getRecentGoods($count);

        if ($product->isEmpty()) {
            throw new ProductException();
        }

        return json($product->hidden(['summary']));
    }

    /**
     * @function   getCategoryDataByCategoryId
     *
     * @example  http://local.article.com/api/v1/product/getCategoryDataByCategoryId/1
     *
     * @param $id
     * @throws \app\lib\exception\ParameterException
     * @author admin
     *
     * @date 2019/5/4
     */
    public function getCategoryDataByCategoryId($id)
    {
        (new IDMustBePositiveInt())->goCheck();

        $categoryData  = productModel::getCategoryDataByCategoryId($id);

        if($categoryData->isEmpty()){
            throw new CategoryException();
        }

        return json($categoryData->hidden(['summary']));

    }

}