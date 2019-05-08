<?php


namespace app\api\service;


use app\api\model\Product;
use app\lib\exception\OrderException;

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

        if(!$status['pass']){
            $status['order_id'] = -1;
            return $status;
        }

        //订单快照
        dump($status);die;

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
}