<?php

namespace app\api\model;

use think\Model;

class Product extends BaseModel
{
    //pivot 多对多关系 中间表数据
    protected $hidden = [
        'delete_time', 'main_img_id', 'pivot',
        'from', 'category_id', 'create_time', 'update_time'
    ];

    public function getMainImgUrlAttr($value, $data)
    {
        return $this->prefixImgUrl($value, $data);
    }


    public static function getRecentGoods($count)
    {
        return self::order('create_time desc')
            ->limit($count)
            ->select();
    }


    public static function getCategoryDataByCategoryId($categoryId)
    {
        return self::where('category_id','=',$categoryId)->select();
    }
}
