<?php


namespace app\api\controller\v1;


use app\api\service\OrderService;
use app\api\service\TokenService;
use app\api\validate\IDMustBePositiveInt;
use app\api\validate\OrderPlaceValidate;
use app\api\validate\PagingParameterValidate;
use app\api\model\Order as orderModel;
use app\lib\exception\OrderException;

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
     * @throws \app\lib\exception\OrderException
     * @throws \app\lib\exception\ParameterException
     * @throws \app\lib\exception\UserException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @author admin
     *
     * @date 2019/5/16 9:06
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

    /**
     * @function   getSummaryByUser 获取 个人的订单详情
     *
     * @param int $page 页数
     * @param int $size 一页显示条数
     * @return array|\think\response\Json
     * @throws \app\lib\exception\ParameterException
     * @author admin
     *
     * @date 2019/5/16 9:52
     */
    public function getSummaryByUser($page = 1, $size = 15)
    {
        (new PagingParameterValidate())->goCheck();
        $uid = TokenService::getCurrentUidByToken();

        $pagingOrders = orderModel::getSummaryByUser($uid, $page, $size);

        if ($pagingOrders->isEmpty()) {
            return [
                'current_page' => $pagingOrders->currentPage(),
                'data' => []
            ];
        }

        $data = $pagingOrders->hidden(['snap_items', 'snap_address'])
            ->toArray();

        return json(['current_page' => $pagingOrders->currentPage(),
            'data' => $data
        ]);
    }

    /**
     * @function   getDetail 得到订单id去获取详情 建议填写 订单编号
     *
     * @param $id   订单id
     * @return \think\response\Json
     * @throws OrderException
     * @throws \app\lib\exception\ParameterException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @author admin
     *
     * @date 2019/5/16 9:55
     */
    public function getDetail($id)
    {
        (new IDMustBePositiveInt())->goCheck();

        $orderDetail = orderModel::get($id);

        if (!$orderDetail) {
            throw new OrderException([
                'msg' => '订单不存在'
            ]);
        }

        return json($orderDetail->hidden(['prepay_id'])->toArray());

    }
}

