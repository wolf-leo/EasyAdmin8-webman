<?php

namespace app\admin\controller;

use app\admin\model\SystemAdmin;
use app\common\controller\AdminController;
use support\Request;
use support\Response;
use think\Exception;
use think\facade\Cache;
use Webman\Captcha\CaptchaBuilder;
use Webman\Captcha\PhraseBuilder;

class LoginController extends AdminController
{

    public function index(Request $request): Response
    {
        $captcha = env('EASYADMIN.CAPTCHA', false);
        if (!$request->isAjax()) {
            return $this->fetch('', compact('captcha'));
        }
        Cache::clear();
        $post = $request->post();
        $rule = [
            'username|用户名' => 'require',
            'password|密码'   => 'require',
        ];
        try {
            $this->validate($post, $rule);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }
        if ($captcha) {
            if (strtolower($request->post('captcha')) !== $request->session()->get('captcha')) {
                return $this->error('图片验证码错误');
            }
        }
        $admin = SystemAdmin::where(['username' => $post['username']])->find();
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

    /**
     * 输出验证码图像
     */
    public function captcha(Request $request): Response
    {
        $length  = 4;
        $chars   = '0123456789';
        $phrase  = new PhraseBuilder($length, $chars);
        $builder = new CaptchaBuilder(null, $phrase);
        // 生成验证码
        $builder->build();
        // 将验证码的值存储到session中
        $request->session()->set('captcha', strtolower($builder->getPhrase()));
        // 获得验证码图片二进制数据
        $img_content = $builder->get();
        // 输出验证码二进制数据
        return response($img_content, 200, ['Content-Type' => 'image/jpeg']);
    }

    public function out(Request $request): Response
    {
        $request->session()->forget('admin');
        if ($request->isAjax()) {
            return $this->success('退出登录成功', [], __url('/login'));
        }
        return redirect(__url('/login'));
    }
}
