<?php

namespace app\admin\controller\system;

use app\admin\model\SystemConfig;
use common\controller\AdminController;
use common\services\annotation\ControllerAnnotation;
use common\services\annotation\NodeAnnotation;
use common\services\TriggerService;
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
        $this->model  = new SystemConfig();
        $upload_types = config('admin.upload_types');
        $this->assign(compact('upload_types'));
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
        $post         = $request->post();
        $notAddFields = ['_token', 'file', 'group'];
        try {
            $group = $post['group'] ?? '';
            if (empty($group)) return $this->error('保存失败');
            if ($group == 'upload') {
                $upload_types = config('admin.upload_types');
                // 兼容旧版本
                $this->model->where('name', 'upload_allow_type')->update(['value' => implode(',', array_keys($upload_types))]);
            }
            foreach ($post as $key => $val) {
                if (in_array($key, $notAddFields)) continue;
                if ($this->model->where('name', $key)->count()) {
                    $this->model->where('name', $key)->update(['value' => $val,]);
                } else {
                    if (empty($key)) continue;
                    $this->model->insert(
                        [
                            'name'  => $key,
                            'value' => $val,
                            'group' => $group,
                        ]);
                }
            }
            TriggerService::updateSysconfig();
        } catch (\Exception $e) {
            return $this->error('保存失败:' . $e->getMessage());
        }
        return $this->success('保存成功');
    }

}
