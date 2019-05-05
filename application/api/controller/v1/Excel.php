<?php


namespace app\api\controller\v1;


use app\api\service\ExcelService;

class Excel
{

    protected static $objPHPExcel;

    public function __construct()
    {
        self::$objPHPExcel = new ExcelService();
    }

    public function generalExcel()
    {

        $field = [
            'A' => ['id', 'ID',false,20],
            'B' => ['user_id', '用户ID',false,20],
            'C' => ['uname', '用户名称',false,20],
            'D' => ['created', '时间',false,20],
            'E' => ['order_id', '订单号',false,20],
            'F' => ['price', '金额(元)',false,20],
            'G' => ['coins', '充值金币数',false,20],
            'H' => ['device_id', '设备ID',false,20],
            'I' => ['ktv_id', 'ktvID',false,20],
            'J' => ['kname', 'ktv名称',false,20],
            'K' => ['status', '状态(paid:已付款,refunded:已退款,pending:处理中)',true,35]
        ];

        $data = [
            [
                'id' => 1,
                'user_id' => 90001,
                'uname' => '小坤',
                'created' => '2019-04-11 16:32:32',
                'order_id' => 001122,
                'price' => 998,
                'coins' => 1,
                'device_id' => 1,
                'ktv_id' => 1,
                'kname' => 1,
                'status' => 1,
            ], [
                'id' => 2,
                'user_id' => 90001,
                'uname' => '小坤',
                'created' => '2019-04-11 16:32:32',
                'order_id' => 001122,
                'price' => 998,
                'coins' => 1,
                'device_id' => 1,
                'ktv_id' => 1,
                'kname' => 1,
                'status' => 1,
            ],
        ];

        self::$objPHPExcel->generalExcel($field, $data, '充值列表_' . date('Y-m-d'));
    }


}