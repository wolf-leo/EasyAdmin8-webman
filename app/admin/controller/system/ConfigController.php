<?php

namespace app\admin\controller\system;

use app\admin\model\SystemConfig;
use app\common\controller\AdminController;
use app\common\services\annotation\ControllerAnnotation;
use app\common\services\annotation\NodeAnnotation;
use app\common\services\TriggerService;
use support\Request;
use support\Response;

/**
 * @ControllerAnnotation(title="系统配置管理")
 */
class ConfigController extends AdminController
{

    public function initialize()
    {
        parent::initialize();
        $this->model = new SystemConfig();
    }

    /**
     * @NodeAnnotation(title="列表")
     */
    public function index(Request $request): Response
    {
        return $this->fetch();
    }

    /**
     * @NodeAnnotation(title="保存")
     */
    public function save(Request $request): Response
    {
        if (!$request->isAjax()) return $this->error();
        $post = $request->post();
        try {
            foreach ($post as $key => $val) {
                if (in_array($key, ['file', 'files'])) continue;
                $this->model->where('name', $key)->update(['value' => $val,]);
            }
            TriggerService::updateSysconfig();
        } catch (\Exception $e) {
            return $this->error('保存失败:' . $e->getMessage());
        }
        return $this->success('保存成功');
    }

}
