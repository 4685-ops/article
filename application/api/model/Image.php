<?php


namespace app\api\model;


class Image extends BaseModel
{
    protected $hidden = ['delete_time', 'id', 'from', 'update_time'];

    /**
     * @function   getUrlAttr 修改返回图片的路径
     *
     * @param $value
     * @param $data
     * @return string
     * @author admin
     *
     * @date 2019/4/26 13:57
     */
    public function getUrlAttr($value, $data)
    {
        return $this->prefixImgUrl($value, $data);
    }
}