<?php

namespace app\admin\controller\mall;

use app\admin\model\MallCate;
use common\controller\AdminController;
use common\services\annotation\ControllerAnnotation;
use common\services\annotation\NodeAnnotation;

/**
 * @ControllerAnnotation(title="商品分类管理")
 */
class CateController extends AdminController
{

    public function initialize()
    {
        parent::initialize();
        $this->model = new MallCate();
    }

}
