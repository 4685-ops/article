<?php


namespace app\api\service;


use app\api\model\Product;
use app\lib\enum\OrderStatusEnum;
use think\Db;
use think\Exception;
use think\Loader;
use app\api\model\Order as orderModel;
use think\Log;

Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');

class WxNotify extends \WxPayNotify
{
    //protected $data = <<<EOD
    //<xml><appid><![CDATA[wxaaf1c852597e365b]]></appid>
    //<bank_type><![CDATA[CFT]]></bank_type>
    //<cash_fee><![CDATA[1]]></cash_fee>
    //<fee_type><![CDATA[CNY]]></fee_type>
    //<is_subscribe><![CDATA[N]]></is_subscribe>
    //<mch_id><![CDATA[1392378802]]></mch_id>
    //<nonce_str><![CDATA[k66j676kzd3tqq2sr3023ogeqrg4np9z]]></nonce_str>
    //<openid><![CDATA[ojID50G-cjUsFMJ0PjgDXt9iqoOo]]></openid>
    //<out_trade_no><![CDATA[A301089188132321]]></out_trade_no>
    //<result_code><![CDATA[SUCCESS]]></result_code>
    //<return_code><![CDATA[SUCCESS]]></return_code>
    //<sign><![CDATA[944E2F9AF80204201177B91CEADD5AEC]]></sign>
    //<time_end><![CDATA[20170301030852]]></time_end>
    //<total_fee>1</total_fee>
    //<trade_type><![CDATA[JSAPI]]></trade_type>
    //<transaction_id><![CDATA[4004312001201703011727741547]]></transaction_id>
    //</xml>
    //EOD;

    /**
     * @function   NotifyProcess    改写原本微信支付的通知类
     *
     * @param array $data
     * @param string $msg
     * @return \true回调出来完成不需要继续回调，false回调处理未完成需要继续回调|void
     * @author admin
     *
     * @date 2019/5/15 9:25
     */
    public function NotifyProcess($data, &$msg)
    {
        // post xml格式 不会携带参数 微信返回的一般是xml数据 是不会给你再地址栏拼接参数的
        //1.检查库存量 超卖可能性特别小 2.更新订单的状态3.减少库存
        //4.如果成功处理 返回微信成功处理的通知 否则我们需要返回没用成功处理的
        if ($data['result_code'] == "SUCCESS") {
            //  微信返回成功 获取订单编号
            $orderNo = $data['out_trade_no'];
            Db::startTrans();//开启事务
            try {
                $order = orderModel::where('order_no', '=', $orderNo)->lock(true)->find();//lock加锁
                //必须是未支付
                if ($order->status == OrderStatusEnum::UNPAID) {
                    $orderService = new OrderService();
                    $status = $orderService->checkOrderStock($order->id); //检查库存量
                    //库存量检查通过
                    if ($status['pass']) {
                        $this->updateOrderStatus($order->id, true);//更新订单的状态
                        $this->reduceStock($status);//减少库存
                    } else {
                        $this->updateOrderStatus($order->id, false); //已经支付了 但没有库存了
                    }
                }
                Db::commit();
                return true;
            } catch (Exception $ex) {
                Db::rollback();
                Log::error($ex);
                return false;
            }
        }
        return true;
    }

    /**
     * @function   updateOrderStatus    更新订单状态
     *
     * @remake     如果库存通过 订单状态改为2已支付  没有通过改为4 已支付，但库存不足
     *
     * @param $orderId
     * @param $success
     * @author admin
     *
     * @date 2019/5/15 9:57
     */
    private function updateOrderStatus($orderId, $success)
    {
        $status = $status ? OrderStatusEnum::PAID : OrderStatusEnum::PAID_BUT_OUT_OF;
        orderModel::where('id', '=', $orderId)->update(['status' => $status]);
    }

    /**
     * @function   reduceStock  减少库存
     *
     * @param $status
     * @throws Exception
     * @author admin
     *
     * @date 2019/5/15 9:56
     */
    private function reduceStock($status)
    {
        foreach ($status['pStatusArray'] as $singlePStatus) {
            Product::where('id', '=', $singlePStatus['id'])
                ->setDec('stock', $singlePStatus['count']);
        }
    }
}