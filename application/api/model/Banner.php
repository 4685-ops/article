<?php


namespace app\api\model;


class Banner extends BaseModel
{

    protected $hidden = ['id', 'name', 'description', 'delete_time', 'update_time'];

    /**
     * @function   items    关联表
     *
     * @return \think\model\relation\HasMany
     * @author admin
     *
     * @date 2019/4/26 13:50
     */
    public function items()
    {
        /*
         * hasMany('关联模型名','外键名','主键名',['模型别名定义']);
         */
        return $this->hasMany('BannerItem', 'banner_id', 'id');
    }

    /**
     * @function   getBannerItemInfoByBannerId  得到banner表id获取 当前id下的详情
     *
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author admin
     *
     * @date 2019/4/26 13:50
     */
    public static function getBannerItemInfoByBannerId($id)
    {

        $data = self::with([
            'items', 'items.img'
        ])->find($id);

        return $data;
    }



}

