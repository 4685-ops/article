<?php


namespace app\api\model;


class Order extends BaseModel
{
    protected $hidden = ['user_id', 'delete_time', 'update_time'];
    protected $autoWriteTimestamp = true;

    public static function getSummaryByUser($uid, $page, $size)
    {
        return self::where('user_id', '=', $uid)
            ->order("create_time desc")
            ->paginate($size, true, ['page' => $page]);
    }


    public function getSnapItemsAttr($value)
    {
        if (empty($value))
            return null;
        $returnData = unserialize($value);

        return $returnData;
    }

    public function getSnapAddressAttr($value)
    {

        if (empty($value))
            return null;

        return json_decode($value);
    }

}