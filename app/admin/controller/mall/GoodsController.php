<?php

namespace app\admin\controller\mall;

use app\admin\model\MallGoods;
use app\common\controller\AdminController;
use app\common\services\annotation\ControllerAnnotation;
use app\common\services\annotation\NodeAnnotation;
use support\Request;
use support\Response;

/**
 * @ControllerAnnotation(title="商城商品管理")
 */
class GoodsController extends AdminController
{

    public function initialize()
    {
        parent::initialize();
        $this->model = new MallGoods();
    }

    /**
     * @NodeAnnotation(title="列表")
     */
    public function index(Request $request): Response
    {
        if (!$request->isAjax()) return $this->fetch();
        list($page, $limit, $where) = $this->buildTableParams();
        $count = $this->model->where($where)->count();
        $list  = $this->model->where($where)->with(['cate'])->order($this->order)->paginate($limit)->items();
        $data  = [
            'code'  => 0,
            'msg'   => '',
            'count' => $count,
            'data'  => $list,
        ];
        return json($data);
    }

    /**
     * @NodeAnnotation(title="入库")
     */
    public function stock(Request $request): Response
    {
        $id  = $request->input('id');
        $row = $this->model->find($id);
        if (empty($row)) return $this->error('数据不存在');
        if ($request->isAjax()) {
            $post = $request->post();
            try {
                $post['total_stock'] = $row->total_stock + $post['stock'];
                $post['stock']       = $row->stock + $post['stock'];
                $save                = $row->save($post);
            } catch (\Exception $e) {
                return $this->error('保存失败');
            }
            return $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $this->assign(compact('row'));
        return $this->fetch();
    }
}
