<?php


namespace app\api\model;


use think\Model;

class BaseModel extends Model
{
    /**
     * @function   prefixImgUrl 获取器的使用
     *
     * @param $value
     * @param $data
     * @return string
     * @author admin
     *
     * @date 2019/4/26 13:56
     */
    public function prefixImgUrl($val, $data)
    {
        $finalUrl = $val;

        /*
         * from  0 是完整的url 1 是本地上传
         */
        if ($data['from'] == 1) {
            $finalUrl = config('wx.img_prefix') . $val;
        }
        return $finalUrl;
    }
}