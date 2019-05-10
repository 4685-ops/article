<?php


namespace app\lib\enum;


class OrderStatusEnum
{
    //1:未支付
    const UNPAID = 1;
    //2：已支付
    const PAID = 2;
    //3：已发货
    const SHIPPED = 3;
    //4: 已支付，但库存不足
    const PAIDINVENTORYSHORTAGE = 4;
}