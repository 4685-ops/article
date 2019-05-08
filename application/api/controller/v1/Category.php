<?php
/**
 * Created by PhpStorm.
 * User: 757208466
 * Date: 2019/5/4
 * Time: 16:16
 */

namespace app\api\controller\v1;


use app\lib\exception\CategoryException;
use think\Controller;

use app\api\model\Category as categoryModel;

class Category extends Controller
{
    /**
     * @function   getAllCategory   得到所有的分类信息
     *
     * @return \think\response\Json
     * @throws CategoryException
     * @author admin
     *
     * @date 2019/5/6 18:22
     */
    public function getAllCategory()
    {
        $category = categoryModel::getAllCategory();

        if ($category->isEmpty()) {
            throw new CategoryException();
        }

        return json($category->hidden(['summary']));
    }
}

