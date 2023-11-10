<?php

namespace app\admin\controller\system;

use app\common\controller\AdminController as Controller;
use app\admin\model\SystemAdmin;
use app\common\services\TriggerService;
use support\Request;
use support\Response;
use Respect\Validation\Validator;
use app\common\services\annotation\ControllerAnnotation;
use app\common\services\annotation\NodeAnnotation;

/**
 * @ControllerAnnotation(title="管理员管理")
 */
class AdminController extends Controller
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new SystemAdmin();
        $auth_list   = $this->model->getAuthList();
        $this->assign(compact('auth_list'));
    }

    /**
     * @NodeAnnotation(title="添加")
     */
    public function add(Request $request): Response
    {
        if ($request->isAjax()) {
            $post               = $request->post();
            $authIds            = $request->post('auth_ids', []);
            $params['auth_ids'] = implode(',', array_keys($authIds));
            if (empty($post['password'])) $post['password'] = '123456';
            $params['password'] = password($post['password']);
            try {
                $save = insertFields($this->model, $params);
            } catch (\Exception $e) {
                return $this->error('保存失败:' . $e->getMessage());
            }
            return $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        return $this->fetch();
    }

    /**
     * @NodeAnnotation(title="编辑")
     */
    public function edit(Request $request): Response
    {
        $id  = $request->input('id');
        $row = $this->model->find($id);
        if (empty($row)) return $this->error('数据不存在');
        if ($request->isAjax()) {
            $post               = $request->post();
            $authIds            = $request->post('auth_ids', []);
            $params['auth_ids'] = implode(',', array_keys($authIds));
            if (isset($row['password'])) unset($row['password']);
            try {
                $save = updateFields($this->model, $row, $params);
                TriggerService::updateMenu(session('admin.id'));
            } catch (\Exception $e) {
                return $this->error('保存失败:' . $e->getMessage());
            }
            return $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $row->auth_ids = explode(',', $row->auth_ids ?: '');
        $this->assign(compact('row'));
        return $this->fetch();
    }

    /**
     * @NodeAnnotation(title="修改密码")
     */
    public function password(Request $request): Response
    {
        $id  = $request->input('id');
        $row = $this->model->find($id);
        if (empty($row)) return $this->error('数据不存在');
        if ($request->isAjax()) {
            $post = $request->post();
            Validator::input($post, [
                'password'       => Validator::notEmpty()->setName('密码'),
                'password_again' => Validator::notEmpty()->setName('确认密码'),
            ]);
            if ($post['password'] != $post['password_again']) {
                return $this->error('两次密码输入不一致');
            }
            if (password($post['password']) == $row->password) {
                return $this->error('新密码不能跟旧密码相同');
            }
            try {
                $save = $this->model->where('id', $id)->update(['password' => password($post['password'])]);
            } catch (\Exception $e) {
                return $this->error('保存失败:' . $e->getMessage());
            }
            return $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $this->assign(compact('row'));
        return $this->fetch();
    }
}
