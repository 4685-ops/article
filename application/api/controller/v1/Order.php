<?php


namespace app\api\controller\v1;


use app\api\service\OrderService;
use app\api\service\TokenService;
use app\api\validate\OrderPlaceValidate;

class Order extends BaseController
{
    // 只有在执行placeorder方法的时候 才会执行前置方法checkExclusiveScope
    // 因为这个接口只有用户才能使用
    protected $beforeActionList = [
        'checkExclusiveScope' => [
            'only' => 'placeorder'
        ]
    ];

    /**
     * @function   placeOrder   用户下单接口
     *
     * @return \think\response\Json
     * @throws \app\lib\exception\ParameterException
     * @author admin
     *
     * @date 2019/5/15 16:10
     */
    public function placeOrder()
    {
        /**  用户在选择商品后 向API提交包含它所选择商品的相关信息
         *  API在接收收到信息后 需要检查订单相关商品的 库存量
         *  有库存 把订单数据存入数据库 等于下单成功了返回客户端信息 ，告诉客户端可以支付了
         *  调用我们的支付接口 进行支付
         *  还需要再次进行 库存量 检测
         *  服务器这边就可以调用微信的支付接口进行支付
         *  微信会返回给我们一个支付结果
         *  成功也需要进行 库存量 检查
         *  根据支付结果 是支付成功了才会扣库存量 失败返回一个支付失败的结果
         **/
        // 1.前台用户数据的传递
        (new OrderPlaceValidate())->goCheck();

        //获取用户id
        $userId = TokenService::getCurrentUidByToken();

        //接收用户购买的记录
        $products = input('post.products/a');

        $orderService = new OrderService();

        $status = $orderService->place($userId, $products);

        return json($status);
    }
}

