<?php

namespace app\admin\controller\system;

use app\admin\model\SystemMenu;
use app\admin\model\SystemNode;
use app\common\controller\AdminController;
use app\common\services\TriggerService;
use support\Request;
use support\Response;
use Respect\Validation\Validator;
use app\common\services\annotation\ControllerAnnotation;
use app\common\services\annotation\NodeAnnotation;

/**
 * @ControllerAnnotation(title="菜单管理")
 */
class MenuController extends AdminController
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new SystemMenu();
    }

    /**
     * @NodeAnnotation(title="添加")
     */
    public function add(Request $request): Response
    {
        $id     = $request->input('id');
        $homeId = $this->model->where(['pid' => HOME_PID,])->value('id');
        if ($id == $homeId) {
            return $this->error('首页不能添加子菜单');
        }
        if ($request->isAjax()) {
            $post = $request->post();
            Validator::input($post, [
                'title'  => Validator::notEmpty()->setName('菜单名称'),
                'icon'   => Validator::notEmpty()->setName('菜单图标'),
                'target' => Validator::notEmpty()->setName('target属性')
            ]);
            try {
                $save = insertFields($this->model);
            } catch (\Exception $e) {
                return $this->error('保存失败');
            }
            if (!empty($save)) {
                TriggerService::updateMenu();
                return $this->success('保存成功');
            } else {
                return $this->error('保存失败');
            }
        }
        $pidMenuList = $this->model->getPidMenuList();
        $this->assign(compact('id', 'pidMenuList'));
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
            $post = $request->post();
            Validator::input($post, [
                'title'  => Validator::notEmpty()->setName('菜单名称'),
                'icon'   => Validator::notEmpty()->setName('菜单图标'),
                'target' => Validator::notEmpty()->setName('target属性')
            ]);
            $params = [];
            if ($row->pid == HOME_PID) $params['pid'] = HOME_PID;
            try {
                $save = updateFields($this->model, $row, $params);
            } catch (\Exception $e) {
                return $this->error('保存失败');
            }
            if (!empty($save)) {
                TriggerService::updateMenu();
                return $this->success('保存成功');
            } else {
                return $this->error('保存失败');
            }
        }
        $pidMenuList = $this->model->getPidMenuList();
        $this->assign(compact('id', 'row', 'pidMenuList'));
        return $this->fetch();
    }

    /**
     * @NodeAnnotation(title="属性修改")
     */
    public function modify(Request $request): Response
    {
        $post = $request->post();
        Validator::input($post, [
            'id'    => Validator::notEmpty()->setName('ID'),
            'field' => Validator::notEmpty()->setName('字段'),
        ]);
        $row = $this->model->find($post['id']);
        if (empty($row)) {
            return $this->error('数据不存在');
        }
        $homeId = $this->model->where(['pid' => HOME_PID])->value('id');
        if ($post['id'] == $homeId && $post['field'] == 'status') {
            return $this->error('首页状态不允许关闭');
        }
        try {
            foreach ($post as $key => $item) if ($key == 'field') $row->$item = $post['value'];
            $row->save();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        TriggerService::updateMenu();
        return $this->success('保存成功');
    }

    /**
     * @NodeAnnotation(title="删除")
     */
    public function delete(Request $request): Response
    {
        if (!$request->isAjax()) return $this->error();
        $id = $request->input('id');
        if (!is_array($id)) $id = (array)$id;
        $row = $this->model->whereIn('id', $id)->get()->toArray();
        if (empty($row)) return $this->error('数据不存在');
        try {
            $save = $this->model->whereIn('id', $id)->delete();
        } catch (\PDOException | \Exception $e) {
            return $this->error('删除失败:' . $e->getMessage());
        }
        if ($save) {
            TriggerService::updateMenu();
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }

    /**
     * @NodeAnnotation(title="添加菜单提示")
     */
    public function getMenuTips(Request $request): Response
    {
        $node = $request->input('keywords');
        $list = SystemNode::where('node', 'Like', "%{$node}%")->limit(10)->select('node', 'title')->get()->toArray();
        return json([
                        'code'    => 0,
                        'content' => $list,
                        'type'    => 'success',
                    ]);
    }
}
