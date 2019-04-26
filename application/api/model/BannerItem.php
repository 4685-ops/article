<?php


namespace app\api\model;


class BannerItem extends BaseModel
{

    protected $hidden = ['delete_time','update_time'];

    public function img()
    {
        /**
         * belongsTo('关联模型名','外键名','主键名',['模型别名定义'],'join类型');
         */
        return $this->belongsTo('Image', 'img_id', 'id');
    }
}