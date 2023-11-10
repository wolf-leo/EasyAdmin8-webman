<?php

namespace app\admin\controller;

use app\admin\model\SystemAdmin;
use app\admin\model\SystemQuick;
use app\common\controller\AdminController;
use think\Exception;
use think\facade\Db;
use support\Request;
use support\Response;

class IndexController extends AdminController
{

    public function index(Request $request): Response
    {
        return $this->fetch();
    }


    public function welcome(Request $request): Response
    {
        $branch        = file_get_contents(base_path() . DIRECTORY_SEPARATOR . 'branch');
        $webmanVersion = \Composer\InstalledVersions::getVersion('workerman/webman-framework');
        $mysqlVersion  = Db::query("select VERSION() as version")[0]['version'] ?? '未知';
        $phpVersion    = phpversion();
        $versions      = compact('webmanVersion', 'mysqlVersion', 'phpVersion');
        $quicks        = SystemQuick::where('status', 1)->field('id,title,icon,href')->order('sort', 'desc')->limit(8)->select()->toArray();
        return $this->fetch('', compact('quicks', 'versions', 'branch'));
    }

    public function editAdmin(Request $request): Response
    {
        $id    = session('admin.id');
        $model = new SystemAdmin();
        $row   = $model->find($id);
        if (empty($row)) return $this->error('用户信息不存在');
        if ($request->isAjax()) {
            if ($this->isDemo) return $this->error('演示环境下不允许修改');
            $post = $request->post();
            try {
                $save = $row
                    ->allowField(['head_img', 'phone', 'remark', 'update_time'])
                    ->save($post);
            } catch (\Exception $e) {
                return $this->error('保存失败:' . $e->getMessage());
            }
            return $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $this->assign(compact('row'));
        return $this->fetch();
    }

    public function editPassword(Request $request): Response
    {
        $id    = session('admin.id');
        $model = new SystemAdmin();
        $row   = $model->find($id);
        if (empty($row)) return $this->error('用户信息不存在');
        if ($request->isAjax()) {
            $post = request()->post();
            if ($this->isDemo) return $this->error('演示环境下不允许修改');
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
            $newPwd = password($post['password']);
            if ($newPwd == $row->password) return $this->error('新旧密码不能相同');
            try {
                $save = $model->where('id', $id)->update(['password' => $newPwd]);
            } catch (\Exception $e) {
                return $this->error('保存失败');
            }
            if ($save) {
                return $this->success('保存成功');
            } else {
                return $this->error('保存失败');
            }
        }
        $this->assign(compact('row'));
        return $this->fetch();
    }
}
