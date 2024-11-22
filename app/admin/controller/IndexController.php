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
                $login_type = $request->post('login_type', 1);
                if ($login_type == 2) {
                    $ga_secret = $model->where('id', $id)->value('ga_secret');
                    if (empty($ga_secret)) return $this->error('请先绑定谷歌验证器');
                }
                $save = updateFields($model, $row);
            }catch (\PDOException $e) {
                return $this->error('保存失败:' . $e->getMessage());
            }
            return $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $notes = (new SystemAdmin())->notes;
        $this->assign(compact('row', 'notes'));
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

    /**
     * 设置谷歌验证码
     * @param Request $request
     */
    public function set2fa(Request $request): Response
    {
        $id  = session('admin.id');
        $row = (new SystemAdmin())->select(['id', 'ga_secret', 'login_type'])->find($id);
        if (!$row) return $this->error('用户信息不存在');
        // You can see: https://gitee.com/wolf-code/authenticator
        $ga = new \Wolfcode\Authenticator\google\PHPGangstaGoogleAuthenticator();
        if (!$request->isAjax()) {
            $old_secret = $row->ga_secret;
            $secret     = $ga->createSecret(32);
            $ga_title   = $this->isDemo ? 'EasyAdmin8-Laravel演示环境' : '可自定义修改显示标题';
            $dataUri    = $ga->getQRCode($ga_title, $secret);
            $this->assign(compact('row', 'dataUri', 'old_secret', 'secret'));
            return $this->fetch();
        }
        if ($this->isDemo) return $this->error('演示环境下不允许修改');
        $post      = $request->post();
        $ga_secret = $post['ga_secret'] ?? '';
        $ga_code   = $post['ga_code'] ?? '';
        if (empty($ga_code)) return $this->error('请输入验证码');
        if (!$ga->verifyCode($ga_secret, $ga_code)) return $this->error('验证码错误');
        $row->ga_secret  = $ga_secret;
        $row->login_type = 2;
        $row->save();
        return $this->success('操作成功');
    }
}
