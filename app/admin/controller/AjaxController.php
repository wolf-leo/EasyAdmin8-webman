<?php

namespace app\admin\controller;

use app\admin\model\SystemUploadfile;
use app\common\controller\AdminController;
use app\common\services\MenuService;
use app\common\services\UploadService;
use think\facade\Cache;
use support\Request;
use support\Response;
use think\Exception;

class AjaxController extends AdminController
{
    /**
     * @desc 初始化导航
     * @return Response
     */
    public function initAdmin(): Response
    {
        $cacheData = Cache::get('initAdmin_' . session('admin.id'));
        if (!empty($cacheData)) {
            return json($cacheData);
        }
        $menuService = new MenuService(session('admin.id', 0));
        $data        = [
            'logoInfo' => [
                'title' => sysconfig('site', 'logo_title'),
                'image' => sysconfig('site', 'logo_image'),
                'href'  => __url(),
            ],
            'homeInfo' => $menuService->getHomeInfo(),
            'menuInfo' => $menuService->getMenuTree(),
        ];
        Cache::set('initAdmin_' . session('admin.id'), $data);
        return json($data);
    }

    /**
     * @desc 清理缓存接口
     * @return Response
     */
    public function clearCache(): Response
    {
        Cache::clear();
        return $this->success('清理缓存成功');
    }

    /**
     * @desc  上传文件
     * @param Request $request
     * @return Response
     */
    public function upload(Request $request): Response
    {
        if ($this->isDemo) return $this->error('演示环境下不允许修改');
        if ($request->method() != 'POST') return $this->error();
        $type         = $request->input('type', '');
        $data         = [
            'upload_type' => $request->post('upload_type', ''),
            'file'        => $request->file($type == 'editor' ? 'upload' : 'file'),
        ];
        $uploadConfig = sysconfig('upload');
        empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
        $rule = [
            'upload_type|指定上传类型' => 'require',
            'file|文件'                => 'require',
        ];
        try {
            $this->validate($data, $rule);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $file = $data['file'];
        if (!in_array($file->getUploadExtension(), explode(',', $uploadConfig['upload_allow_ext']))) {
            return $this->error('上传文件类型不在允许范围');
        }
        if ($file->getSize() > $uploadConfig['upload_allow_size']) {
            return $this->error('文件大小超过预设值');
        }
        $upload_type = $uploadConfig['upload_type'];
        try {
            $upload = UploadService::instance()->setConfig($uploadConfig)->$upload_type($file, $type);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        $code = $upload['code'] ?? 0;
        if ($code == 0) {
            return $this->error($upload['data'] ?? '');
        } else {
            return $type == 'editor' ? json(
                [
                    'error'    => ['message' => '上传成功', 'number' => 201,],
                    'fileName' => '',
                    'uploaded' => 1,
                    'url'      => $upload['data']['url'] ?? '',
                ]
            ) : $this->success('上传成功', $upload['data'] ?? '');
        }
    }

    /**
     * @desc 获取上传文件
     * @param Request $request
     * @return Response
     */
    public function getUploadFiles(Request $request): Response
    {
        $get         = $request->all();
        $limit       = $get['limit'] ?? 10;
        $title       = $get['title'] ?? '';
        $this->model = new SystemUploadfile();
        $where       = [];
        if ($title) $where[] = ['original_name', 'LIKE', "%{$title}%"];
        $count = $this->model->where($where)->count();
        $list  = $this->model->where($where)->order($this->order)->limit($limit)->select()->toArray();
        $data  = [
            'code'  => 0,
            'msg'   => '',
            'count' => $count,
            'data'  => $list,
        ];
        return json($data);
    }
}
