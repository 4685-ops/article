<?php

namespace app\api\model;

use think\Model;

class Product extends BaseModel
{
    //pivot 多对多关系 中间表数据
    protected $hidden = [
        'delete_time', 'main_img_id', 'pivot',
        'from', 'category_id', 'create_time', 'update_time','img_id'
    ];

    public function getMainImgUrlAttr($value, $data)
    {
        return $this->prefixImgUrl($value, $data);
    }

    public function imgs()
    {
        return $this->hasMany('ProductImage', 'product_id', 'id');
    }

    public function property()
    {
        return $this->hasMany('ProductProperty', 'product_id', 'id');
    }

    public static function getRecentGoods($count)
    {
        return self::order('create_time desc')
            ->limit($count)
            ->select();
    }

    public static function getCategoryDataByCategoryId($categoryId)
    {
        return self::where('category_id', '=', $categoryId)->select();
    }



    public static function getProductInfoByProductId($id)
    {
        return self::with(['imgs'=>function($query){
                    $query->with(['imgUrl'])->order('order asc');
                }])
                ->with(['property'])
                ->where('id', '=', $id)
                ->select();
    }
}
