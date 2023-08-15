<?php

namespace app\admin\controller\system;

use app\admin\model\SystemLog;
use common\controller\AdminController;
use common\services\annotation\ControllerAnnotation;
use common\services\annotation\NodeAnnotation;
use support\Request;
use support\Response;

/**
 * @ControllerAnnotation(title="操作日志管理")
 */
class LogController extends AdminController
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new SystemLog();
    }

    public function index(Request $request): Response
    {
        if (!$request->isAjax()) return $this->fetch();
        [$page, $limit, $where, $excludeFields] = $this->buildTableParams(['month']);
        $month = !empty($excludeFields['month']) ? date('Ym', strtotime($excludeFields['month'])) : date('Ym');
        if (empty($month)) $month = date('Ym');
        try {
            $count = $this->model->setMonth($month)->where($where)->count();
            $list  = $this->model->setMonth($month)->with(['admin'])->where($where)->orderByDesc($this->order)->paginate($limit)->items();
        } catch (\PDOException | \Exception $exception) {
            $count = 0;
            $list  = [];
        }
        $data = [
            'code'  => 0,
            'msg'   => '',
            'count' => $count,
            'data'  => $list,
        ];
        return json($data);
    }
}
