<?php


namespace app\api\service;


use app\api\model\Product;
use app\api\model\UserAddress;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use app\api\model\Order;
use app\api\model\OrderProduct;
use think\Exception;

class OrderService
{
    protected $oProducts = [];
    protected $products = [];
    protected $uid = '';


    public function place($uid, $oProducts)
    {
        $this->uid = $uid;
        $this->oProducts = $oProducts;

        //用户传递过来的商品 去数据库查找真正的商品
        $this->products = $this->getProductsByOrder();

        //获取当前订单的状态
        $status = $this->getOrderStatus();

        if (!$status['pass']) {
            $status['order_id'] = -1;
            return $status;
        }

        //订单快照
        $orderSnap = $this->createSnap($status);

        //生成订单
        $status = $this->createOrderByTrans($orderSnap);

        $status['pass'] = true;

        return $status;
    }

    //
    public function createOrderByTrans($snap)
    {
        try {
            $orderModel = new Order();
            $orderModel->order_no = $this->makeOrderNo();
            $orderModel->user_id = $this->uid;
            $orderModel->total_price = $snap['orderPrice'];
            $orderModel->total_count = $snap['totalCount'];
            $orderModel->snap_img = $snap['snapImg'];
            $orderModel->snap_name = $snap['snapName'];
            $orderModel->snap_items = serialize($snap['pStatus']);
            $orderModel->snap_address = $snap['snapAddress'];
            $orderModel->save();//保存订单
            $orderId = $orderModel->id;
            $create_time = $orderModel->create_time;

            foreach ($this->oProducts as &$val) {
                $val['order_id'] = $orderId;
            }

            $orderProductModel = new OrderProduct();
            $orderProductModel->saveAll($this->oProducts);//保存订单商品信息
            return [
                'order_no' => $orderModel->order_no,
                'order_id' => $orderId,
                'create_time' => strtotime($create_time)
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%07d', rand(0, 999999999));
        return $orderSn;
    }


    public function createSnap()
    {
        $snapOrder = [
            'orderPrice' => 0,
            'totalCount' => 0,
            'pStatus' => [],
            'snapAddress' => json_encode($this->getUserAddress()),
            'snapName' => $this->products[0]['name'],
            'snapImg' => $this->products[0]['main_img_url']
        ];

        if (count($this->products) > 1) {
            $snapOrder['snapName'] .= '等';
        }

        for ($i = 0; $i < count($this->products); $i++) {
            $oProduct = $this->oProducts[$i];
            $product = $this->products[$i];

            $pStatus = $this->snapProduct($product, $oProduct['count']);

            $snapOrder['orderPrice'] += $pStatus['totalPrice'];
            $snapOrder['totalCount'] += $pStatus['count'];

            array_push($snapOrder['pStatus'], $pStatus);
        }

        return $snapOrder;
    }

    public function getUserAddress()
    {
        $userAddress = UserAddress::where('user_id', '=', $this->uid)->find();

        if (empty($userAddress)) {
            throw new UserException([
                'msg' => '用户收货地址不存在，下单失败',
                'errorCode' => 60001,
            ]);
        }

        return $userAddress->toArray();
    }

    /**
     * @function   getProductsByOrder   根据用户传递的商品 去数据库查询出相对应的商品
     *
     * @return mixed
     * @throws \think\exception\DbException
     * @author admin
     *
     * @date 2019/5/8 16:00
     */
    protected function getProductsByOrder()
    {
        $oProductIds = [];


        foreach ($this->oProducts as $key => $val) {
            array_push($oProductIds, $val['product_id']);
        }

        //根据商品去获取数据库中商品的数量
        $products = Product::all($oProductIds)->visible(['id', 'price', 'stock', 'name', 'main_img_url'])->toArray();

        return $products;
    }


    /**
     * @function   getOrderStatus 订单的状态
     *
     * @return array
     * @throws OrderException
     * @author admin
     *
     * @date 2019/5/8 16:30
     */
    protected function getOrderStatus()
    {
        $status = [
            'pass' => true,
            'orderPrice' => 0,
            'pStatusArray' => []
        ];

        foreach ($this->oProducts as $oProduct) {
            $pStatus = $this->getProductStatus($oProduct['product_id'], $oProduct['count'], $this->products);

            if (!$pStatus['haveStock']) {
                $status['pass'] = false;
            }
            $status['orderPrice'] += $pStatus['totalPrice'];
            array_push($status['pStatusArray'], $pStatus);
        }

        return $status;

    }

    protected function getProductStatus($oPID, $oCount, $products)
    {
        $pIndex = -1;

        $pStatus = [
            'id' => null,
            'haveStock' => false,
            'count' => 0,
            'name' => null,
            'totalPrice' => 0
        ];

        for ($i = 0; $i < count($products); $i++) {
            if ($products[$i]['id'] == $oPID) {
                $pIndex = $i;
            }
        }

        if ($pIndex == -1) {
            throw new OrderException([
                'msg' => 'id为' . $oPID . '的商品不存在，订单创建失败'
            ]);
        } else {
            $pStatus['id'] = $products[$pIndex]['id'];
            $pStatus['count'] = $oCount;
            $pStatus['name'] = $products[$pIndex]['name'];
            $pStatus['totalPrice'] = $products[$pIndex]['price'] * $oCount;

            if ($products[$pIndex]['stock'] - $oCount >= 0) {
                $pStatus ['haveStock'] = true;
            }
        }

        return $pStatus;
    }

    /**
     * @function   snapProduct  单个商品库存检测
     *
     * @param $product
     * @param $oCount
     * @return array
     * @author admin
     *
     * @date 2019/5/9 9:57
     */
    private function snapProduct($product, $oCount)
    {
        $pStatus = [
            'id' => null,
            'name' => null,
            'main_img_url' => null,
            'count' => $oCount,
            'totalPrice' => 0,
            'price' => 0
        ];

        $pStatus['id'] = $product['id'];
        $pStatus['name'] = $product['name'];
        $pStatus['main_img_url'] = $product['main_img_url'];
        $pStatus['count'] = $oCount;
        $pStatus['totalPrice'] = $product['price'] * $oCount;
        $pStatus['price'] = $product['price'];

        return $pStatus;
    }

    public function checkOrderStock($orderId)
    {
        $this->oProducts = OrderProduct::where('order_id', '=', $orderId)->select();
        $this->products = $this->getProductsByOrder();
        $status = $this->getOrderStatus();
        return $status;
    }
}