<?php

namespace app\admin\controller\system;

use app\admin\model\SystemUploadfile;
use app\common\controller\AdminController;
use app\common\services\annotation\ControllerAnnotation;
use app\common\services\annotation\NodeAnnotation;

/**
 * @ControllerAnnotation(title="上传文件管理")
 */
class UploadfileController extends AdminController
{

    public function initialize()
    {
        parent::initialize();
        $this->model = new SystemUploadfile();
    }

}
