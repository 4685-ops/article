<?php


namespace app\api\controller\v1;


use app\api\validate\IDMustBePositiveInt;
use app\lib\exception\MissException;
use think\Controller;
use app\api\model\Banner as bannerModel;

class Banner extends Controller
{

    /**
     * @function   getBannerItemInfoByBannerId 得到banner表id获取 当前id下的详情
     *
     * @param $id   banner 表中id
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws MissException
     * @throws \app\lib\exception\ParameterException
     * @author admin
     *
     * @date 2019/4/26 13:49
     */
    public function getBannerItemInfoByBannerId($id)
    {

        $bannerValidate = new IDMustBePositiveInt();
        $bannerValidate->goCheck();

        $result = bannerModel::getBannerItemInfoByBannerId($id);

        if (!$result) {
            throw new MissException([
                'msg' => '请求的banner不存在',
                'errorCode' => 40000,
            ]);
        }

        return $result;
    }
}