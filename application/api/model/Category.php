<?php

namespace app\api\model;


class Category extends BaseModel
{
    protected $hidden = ['delete_time','update_time','description','topic_img_id'];
    public function img()
    {
        return $this->belongsTo('Image', 'topic_img_id', 'id');
    }

    public static function getAllCategory(){
        return self::with(['img'])->select();
    }
}