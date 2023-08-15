<?php

namespace app\admin\controller\system;

use app\admin\model\SystemQuick;
use common\controller\AdminController;
use common\services\annotation\ControllerAnnotation;
use common\services\annotation\NodeAnnotation;

/**
 * @ControllerAnnotation(title="快捷入口管理")
 */
class QuickController extends AdminController
{

    public function initialize()
    {
        parent::initialize();
        $this->model = new SystemQuick();
    }

}
