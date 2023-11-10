<?php

namespace app\admin\controller\system;

use app\common\controller\AdminController;
use app\admin\model\SystemNode;
use app\common\services\NodeService;
use app\common\services\TriggerService;
use support\Request;
use support\Response;
use app\common\services\annotation\ControllerAnnotation;
use app\common\services\annotation\NodeAnnotation;

/**
 * @ControllerAnnotation(title="系统节点管理")
 */
class NodeController extends AdminController
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new SystemNode();
    }

    /**
     * @NodeAnnotation(title="列表")
     */
    public function index(Request $request): Response
    {
        if ($request->isAjax()) {
            $count = $this->model->count();
            $list  = $this->model->getNodeTreeList();
            $data  = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
            ];
            return json($data);
        }
        return $this->fetch();
    }

    /**
     * @NodeAnnotation(title="系统节点更新")
     */
    public function refreshNode(Request $request): Response
    {
        $force = $request->input('force');
        if (!$request->isAjax()) return $this->error();
        $nodeList = (new NodeService())->getNodeList();
        if (empty($nodeList)) return $this->error('暂无需要更新的系统节点');
        $model = new SystemNode();
        try {
            if ($force == 1) {
                $where[]        = ['node', 'IN', array_column($nodeList, 'node')];
                $updateNodeList = $model->where($where)->select()->toArray();
                $formatNodeList = [];
                array_map(function ($value) use (&$formatNodeList) {
                    $formatNodeList[$value['node']]['title']   = $value['title'];
                    $formatNodeList[$value['node']]['is_auth'] = $value['is_auth'];
                }, $nodeList);
                foreach ($updateNodeList as $vo) {
                    if (isset($formatNodeList[$vo['node']])) {
                        $model->where('id', $vo['id'])->update(
                            [
                                'title'   => $formatNodeList[$vo['node']]['title'],
                                'is_auth' => $formatNodeList[$vo['node']]['is_auth'],
                            ]
                        );
                    }
                }
            }
            $existNodeList = $model->field('node,title,type,is_auth')->select()->toArray();
            foreach ($nodeList as $key => &$vo) {
                $vo['create_time'] = $vo['update_time'] = time();
                foreach ($existNodeList as $v) {
                    if ($vo['node'] == $v['node']) {
                        unset($nodeList[$key]);
                        break;
                    }
                }
            }
            $model->saveAll($nodeList);
            TriggerService::updateNode();
        } catch (\Exception $e) {
            return $this->error('节点更新失败:' . $e->getMessage());
        }
        return $this->success('节点更新成功');
    }

    /**
     * @NodeAnnotation(title="清除失效节点")
     */
    public function clearNode(Request $request): Response
    {
        if (!$request->isAjax()) return $this->error();
        $nodeList = (new NodeService())->getNodeList();
        $model    = new SystemNode();
        try {
            $existNodeList  = $model->field('id,node,title,type,is_auth')->select()->toArray();
            $formatNodeList = [];
            array_map(function ($value) use (&$formatNodeList) {
                $formatNodeList[$value['node']] = $value['title'];
            }, $nodeList);
            foreach ($existNodeList as $vo) {
                !isset($formatNodeList[$vo['node']]) && $model->where('id', $vo['id'])->delete();
            }
            TriggerService::updateNode();
        } catch (\Exception $e) {
            return $this->error('节点更新失败:' . $e->getMessage());
        }
        return $this->success('节点更新成功');
    }
}
