<?php

namespace app\api\controller\v1;

use app\api\validate\IDCollection;
use app\api\validate\IDMustBePositiveInt;
use app\lib\exception\ThemeException;
use think\Controller;
use think\Request;
use app\api\model\Theme as themeModel;

class Theme extends Controller
{
    /**
     * @function   getThemeByIds    得到所有的主题信息
     *
     * http://xxx.domain.com/api/v1/Theme/getThemeByIds/ids/1,3,5
     *
     * @param string $ids
     * @return string|\think\response\Json
     * @throws ThemeException
     * @throws \app\lib\exception\ParameterException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author admin
     *
     * @date 2019/5/3
     */
    public function getThemeByIds($ids = "")
    {
        (new IDCollection())->goCheck();

        $ids = explode(',', $ids);

        $data = themeModel::with('topicImgId,headImgId')->select($ids);

        if (empty($data)) {
            Throw new ThemeException();
        }

        return json($data);
    }

    /**
     * @function   getThemeInfoByThemeId  得到单个主题下面的所有数据
     *
     * @param string $id
     * @return string|\think\response\Json
     * @throws \app\lib\exception\ParameterException
     * @author admin
     *
     * http://local.article.com/api/v1/Theme/getThemeInfoByThemeId/1
     * @date 2019/5/3
     */
    public function getThemeInfoByThemeId($id = '')
    {
        (new IDMustBePositiveInt())->goCheck();

        $data = themeModel::getThemeInfoByThemeId($id);

        return json($data);
    }
}
