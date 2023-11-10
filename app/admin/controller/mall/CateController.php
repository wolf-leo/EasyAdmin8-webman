<?php

namespace app\admin\controller\mall;

use app\admin\model\MallCate;
use app\common\controller\AdminController;
use app\common\services\annotation\ControllerAnnotation;
use app\common\services\annotation\NodeAnnotation;

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
