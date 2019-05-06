<?php

namespace app\api\model;

use think\Model;

class Theme extends BaseModel
{
    protected $hidden = ['delete_time', 'update_time','head_img_id','head_img_id'];

    public function topicImgId()
    {
        return $this->belongsTo('Image', 'topic_img_id', 'id');
    }

    public function headImgId()
    {
        return $this->belongsTo('Image', 'head_img_id', 'id');
    }

    public function product()
    {
        return $this->belongsToMany('Product', 'theme_product', 'product_id', 'theme_id');
    }

    public static function getThemeInfoByThemeId($id)
    {
        return self::with('product,topicImgId,product')->find($id);
    }
}
