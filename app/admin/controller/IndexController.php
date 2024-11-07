<?php

namespace app\admin\controller;

use app\admin\model\SystemAdmin;
use app\admin\model\SystemQuick;
use app\common\controller\AdminController;
use support\Db;
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
        static $versions;
        if (empty($versions)) {
            $branch        = json_decode(file_get_contents('./composer.json'))->branch ?? 'main';
            $webmanVersion = \Composer\InstalledVersions::getVersion('workerman/webman-framework');
            $mysqlVersion  = Db::select("select VERSION() as version")[0]->version ?? '未知';
            $phpVersion    = phpversion();
            $versions      = compact('webmanVersion', 'mysqlVersion', 'phpVersion', 'branch');
        }
        $quick_list = SystemQuick::where('status', 1)->select('id', 'title', 'icon', 'href')->orderByDesc('sort')->limit(50)->get()->toArray();
        $quicks     = array_chunk($quick_list, 8);
        return $this->fetch('', compact('quicks', 'versions'));
    }

    public function editAdmin(Request $request): Response
    {
        $id    = session('admin.id');
        $model = new SystemAdmin();
        $row   = $model->find($id);
        if (empty($row)) return $this->error('用户信息不存在');
        if ($request->isAjax()) {
            if ($this->isDemo) return $this->error('演示环境下不允许修改');
            try {
                $save = updateFields($model, $row);
            }catch (\Exception $e) {
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
            $rules     = [
                'password'       => 'required',
                'password_again' => 'required',
            ];
            $validator = Validator::make($post, $rules, [
                'password'       => '密码不能为空或格式错误',
                'password_again' => '确认密码不能为空或格式错误',
            ]);
            if ($validator->fails()) {
                return $this->error($validator->errors()->first());
            }
            if ($post['password'] != $post['password_again']) {
                return $this->error('两次密码输入不一致');
            }
            $newPwd = password($post['password']);
            if ($newPwd == $row->password) return $this->error('新旧密码不能相同');
            try {
                $save = $model->where('id', $id)->update(['password' => $newPwd]);
            }catch (\Exception $e) {
                return $this->error('保存失败');
            }
            if ($save) {
                return $this->success('保存成功');
            }else {
                return $this->error('保存失败');
            }
        }
        $this->assign(compact('row'));
        return $this->fetch();
    }
}
