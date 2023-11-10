<?php

namespace app\admin\controller\system;

use app\admin\model\SystemAuth;
use app\admin\model\SystemAuthNode;
use app\common\controller\AdminController;
use app\common\services\TriggerService;
use support\Request;
use support\Response;
use app\common\services\annotation\ControllerAnnotation;
use app\common\services\annotation\NodeAnnotation;

/**
 * @ControllerAnnotation(title="角色权限管理")
 */
class AuthController extends AdminController
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new SystemAuth();
    }

    /**
     * @NodeAnnotation(title="授权")
     */
    public function authorizes(Request $request): Response
    {
        $id  = $request->input('id');
        $row = $this->model->find($id);
        if (empty($row)) return $this->error('数据不存在');
        if ($request->isAjax()) {
            $list = $this->model->getAuthorizeNodeListByAdminId($id);
            return $this->success('获取成功', $list);
        }
        $this->assign(compact('row'));
        return $this->fetch();
    }

    /**
     * @NodeAnnotation(title="授权保存")
     */
    public function saveAuthorize(Request $request): Response
    {
        if (!$request->isAjax()) return $this->error();
        $id   = request()->input('id');
        $node = request()->post('node', "[]");
        $node = json_decode($node, true);
        $row  = $this->model->find($id);
        if (empty($row)) return $this->error('数据不存在');
        try {
            $authNode = new SystemAuthNode();
            $authNode->where('auth_id', $id)->delete();
            if (!empty($node)) {
                $saveAll = [];
                foreach ($node as $vo) {
                    $saveAll[] = [
                        'auth_id' => $id,
                        'node_id' => $vo,
                    ];
                }
                $authNode->saveAll($saveAll);
            }
            TriggerService::updateMenu();
        } catch (\Exception $e) {
            return $this->error('保存失败:' . $e->getMessage());
        }
        return $this->success('保存成功');
    }
}
