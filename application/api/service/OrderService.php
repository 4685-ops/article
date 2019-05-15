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
    /**
     * 用户下单商品信息
     */
    protected $oProducts = [];
    /**
     * 用户下单商品信息在数据库中真实商品信息
     */
    protected $products = [];
    /**
     * 用户id
     */
    protected $uid = '';

    /**
     * @function   place    订单下单接口
     *
     * @param $uid          用户id
     * @param $oProducts    用户传递的订单信息
     * @return array
     * @throws Exception
     * @throws OrderException
     * @throws UserException
     * @throws \think\exception\DbException
     * @author admin
     *
     * @date 2019/5/15 17:45
     */
    public function place($uid, $oProducts)
    {
        $this->uid = $uid;

        // 用户传过来的商品
        $this->oProducts = $oProducts;

        //用户传递过来的商品 去数据库查找真正的商品
        $this->products = $this->getProductsByOrder();

        //获取当前订单的状态
        $status = $this->getOrderStatus();

        if (!$status['pass']) {
            // 一旦库存量检测不通过 订单id返回-1 后面就不执行其他操作了
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

    /**
     * @function   createOrderByTrans   生成订单信息
     *
     * @param $snap
     * @return array
     * @throws Exception
     * @author admin
     *
     * @date 2019/5/15 17:32
     */
    public function createOrderByTrans($snap)
    {
        // 如果害怕出错 可以开启事务
        try {
            //实例化 OrderModel
            $orderModel = new Order();
            $orderModel->order_no = $this->makeOrderNo();//获取订单编号唯一的字符串
            $orderModel->user_id = $this->uid; //用户id
            $orderModel->total_price = $snap['orderPrice'];//订单的总价
            $orderModel->total_count = $snap['totalCount'];//订单的总个数
            $orderModel->snap_img = $snap['snapImg'];//订单中商品图片
            $orderModel->snap_name = $snap['snapName'];//订单中商品名字
            $orderModel->snap_items = serialize($snap['pStatus']);//订单其他信息快照（json)
            $orderModel->snap_address = $snap['snapAddress'];//地址快照
            $orderModel->save();//保存订单
            $orderId = $orderModel->id;// 获取表中订单id
            $create_time = $orderModel->create_time; // 订单创建时间

            // 订单与商品之间是多对多的关系 所以要往中间表中添加数据
            // 观察 中间表  订单id 商品id 购买这个商品的总数
            foreach ($this->oProducts as &$val) {
                $val['order_id'] = $orderId;//所有吧订单id添加到用户提交的信息之中
            }

            //保存订单商品表信息
            $orderProductModel = new OrderProduct();
            $orderProductModel->saveAll($this->oProducts);
            return [
                'order_no' => $orderModel->order_no,
                'order_id' => $orderId,
                'create_time' => strtotime($create_time)
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @function   makeOrderNo  创建订单编号
     *
     * @return string
     * @author admin
     *
     * @date 2019/5/15 17:46
     */
    public function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%07d', rand(0, 999999999));
        return $orderSn;
    }

    /**
     * @function   createSnap   创建快照
     *
     * @return array
     * @throws Exception
     * @throws UserException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author admin
     *
     * @date 2019/5/15 17:46
     */
    public function createSnap()
    {
        $snapOrder = [
            'orderPrice' => 0,  // 订单总价
            'totalCount' => 0,  // 订单总数
            'pStatus' => [],    // 订单中每个商品的数据 比如 id 是否有库存 数量 金额
            'snapAddress' => json_encode($this->getUserAddress()), // 用户地址
            'snapName' => $this->products[0]['name'],   // 商品名 多个用等拼接
            'snapImg' => $this->products[0]['main_img_url'] // 商品图片
        ];

        if (count($this->products) > 1) {
            $snapOrder['snapName'] .= '等';
        }

        // 遍历数据库中的商品
        for ($i = 0; $i < count($this->products); $i++) {

            $oProduct = $this->oProducts[$i];
            $product = $this->products[$i];

            //生成快照数据
            $pStatus = $this->snapProduct($product, $oProduct['count']);

            $snapOrder['orderPrice'] += $pStatus['totalPrice'];
            $snapOrder['totalCount'] += $pStatus['count'];
            // 有多个商品的时候 这个pStatus 是一个数组
            array_push($snapOrder['pStatus'], $pStatus);
        }

        return $snapOrder;
    }

    /**
     * @function   getUserAddress   获取用户地址
     *
     * @return array
     * @throws Exception
     * @throws UserException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author admin
     *
     * @date 2019/5/15 17:29
     */
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
            'pass' => true,// pass参数用来判断这个订单的库存是否通过
            'orderPrice' => 0,// 订单的总价格
            'pStatusArray' => [] // 用于保存订单的快照信息
        ];

        // 遍历用户传过来的订单数据
        foreach ($this->oProducts as $oProduct) {
            // 用户传递过来的订单数据 获取其中的某一个 去和数据库中的对比 查看是否有库存
            $pStatus = $this->getProductStatus($oProduct['product_id'], $oProduct['count'], $this->products);

            if (!$pStatus['haveStock']) {
                $status['pass'] = false;
            }
            //计算总价
            $status['orderPrice'] += $pStatus['totalPrice'];

            array_push($status['pStatusArray'], $pStatus);
        }

        return $status;

    }

    /**
     * @function   getProductStatus 判断商品的库存状态
     *
     * @param $oPID         用户订单商品中单个商品id
     * @param $oCount       用户订单商品中单个商品购买次数
     * @param $products     根据用户提交的商品获取在数据库中的真实商品信息
     * @return array
     * @throws OrderException
     * @author admin
     *
     * @date 2019/5/15 16:50
     */
    protected function getProductStatus($oPID, $oCount, $products)
    {
        $pIndex = -1;  // 用于记录一下 用户购买的商品在数据库中商品的位置

        $pStatus = [
            'id' => null, //商品id
            'haveStock' => false,//是否有库存 true有库存 false没有库存
            'count' => 0,   // 这件商品买了几个
            'name' => null, // 商品名
            'totalPrice' => 0   // 买这件商品花费的金额
        ];

        //用于记录一下 用户购买的商品在数据库中商品的位置
        for ($i = 0; $i < count($products); $i++) {
            if ($products[$i]['id'] == $oPID) {
                $pIndex = $i;
            }
        }


        if ($pIndex == -1) {
            //如果值还是-1 就说明这件商品不再服务器当中 异常
            throw new OrderException([
                'msg' => 'id为' . $oPID . '的商品不存在，订单创建失败'
            ]);
        } else {
            $pStatus['id'] = $products[$pIndex]['id'];
            $pStatus['count'] = $oCount;
            $pStatus['name'] = $products[$pIndex]['name'];
            $pStatus['totalPrice'] = $products[$pIndex]['price'] * $oCount;
            // 检查库存量
            if ($products[$pIndex]['stock'] - $oCount >= 0) {
                $pStatus ['haveStock'] = true;
            }
        }

        return $pStatus;
    }

    /**
     * @function   snapProduct  单个商品库存检测
     *
     * @param $product  数据库中单个商品详情
     * @param $oCount   用户这个商品买的个数
     * @return array
     * @author admin
     *
     * @date 2019/5/9 9:57
     */
    private function snapProduct($product, $oCount)
    {
        $pStatus = [
            'id' => null, //商品id
            'name' => null, //商品名
            'main_img_url' => null, //商品图片路径
            'count' => $oCount, //这个商品 购买了几次
            'totalPrice' => 0, // 这个商品购买的总价格
            'price' => 0    // 这个商品的单价
        ];

        $pStatus['id'] = $product['id'];
        $pStatus['name'] = $product['name'];
        $pStatus['main_img_url'] = $product['main_img_url'];
        $pStatus['count'] = $oCount;
        $pStatus['totalPrice'] = $product['price'] * $oCount;
        $pStatus['price'] = $product['price'];

        return $pStatus;
    }

    /**
     * @function   checkOrderStock  检查当前的订单的库存
     *
     * @param $orderId
     * @return array
     * @throws OrderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author admin
     *
     * @date 2019/5/13 15:49
     */
    public function checkOrderStock($orderId)
    {
        $this->oProducts = OrderProduct::where('order_id', '=', $orderId)->select();
        $this->products = $this->getProductsByOrder();
        $status = $this->getOrderStatus();
        return $status;
    }
}