<?php

namespace app\admin\controller\system;

use app\common\controller\AdminController as Controller;
use app\admin\model\SystemAdmin;
use app\common\services\TriggerService;
use support\Request;
use support\Response;
use app\common\services\annotation\ControllerAnnotation;
use app\common\services\annotation\NodeAnnotation;
use think\Exception;

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
            $post             = $request->post();
            $authIds          = $request->post('auth_ids', []);
            $post['auth_ids'] = implode(',', array_keys($authIds));
            if (empty($post['password'])) $post['password'] = '123456';
            $post['password'] = password($post['password']);
            try {
                $save = $this->model->save($post);
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
            $post             = $request->post();
            $authIds          = $request->post('auth_ids', []);
            $post['auth_ids'] = implode(',', array_keys($authIds));
            if (isset($row['password'])) unset($row['password']);
            try {
                $save = $row->save($post);
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
            $rule = [
                'password|密码'           => 'require',
                'password_again|确认密码' => 'require',
            ];
            try {
                $this->validate($post, $rule);
            } catch (Exception $exception) {
                return $this->error($exception->getMessage());
            }
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
