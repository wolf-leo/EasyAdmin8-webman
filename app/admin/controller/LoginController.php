<?php

namespace app\admin\controller;

use app\admin\model\SystemAdmin;
use app\common\controller\AdminController;
use support\Request;
use support\Response;
use Respect\Validation\Validator;

class LoginController extends AdminController
{

    public function index(Request $request): Response
    {
        if (!$request->isAjax()) {
            $captcha = env('EASYADMIN.CAPTCHA', 1);
            return $this->fetch('', compact('captcha'));
        }

        $post  = $request->post();
        $rules = [
            'username'   => 'required',
            'password'   => 'required',
            'keep_login' => 'required',
        ];
        Validator::input($post, [
            'username' => Validator::notEmpty()->setName('用户名'),
            'password' => Validator::notEmpty()->setName('密码')
        ]);
        $admin = SystemAdmin::where(['username' => $post['username']])->first();
        if (empty($admin) || password($post['password']) != $admin->password) {
            return $this->error('用户名或密码有误');
        }
        if ($admin->status == 0) {
            return $this->error('账号已被禁用');
        }
        $admin->login_num   += 1;
        $admin->update_time = time();
        $admin->save();
        $admin = $admin->toArray();
        unset($admin['password']);
        $admin['expire_time'] = $post['keep_login'] == 1 ? true : time() + 7200;
        session(compact('admin'));
        return $this->success('登录成功', [], __url());
    }

    public function out(Request $request): Response
    {
        $request->session()->forget('admin');
        return $this->success('退出登录成功', [], __url('/login'));
    }
}
