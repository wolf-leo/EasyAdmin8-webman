<?php

namespace app\common\traits;

use support\Response;

trait JumpTrait
{
    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $msg 提示信息
     * @param array $data 返回的数据
     * @param string|null $url 跳转的 URL 地址
     * @param int $wait 跳转等待时间
     * @return Response
     */
    protected function success(string $msg = '操作成功', array $data = [], string $url = null, int $wait = 3): Response
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } elseif ($url) {
            $url = (strpos($url, '://') || str_starts_with($url, '/')) ? $url : '';
        }
        if (empty($url)) $url = __url();
        $result = [
            'code'      => 1,
            'msg'       => $msg,
            'data'      => $data,
            'url'       => $url,
            'wait'      => $wait,
            '__token__' => '',
        ];
        if ($this->getResponseType() == "html") return view('admin.success', $result);
        return json($result);
    }

    /**
     * @param string $msg
     * @param array $data
     * @param string|null $url
     * @param int $wait
     * @return Response
     */
    public function error(string $msg = '操作失败', array $data = [], string $url = null, int $wait = 3): Response
    {
        if (is_null($url)) {
            $url = request()->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url) {
            $url = (strpos($url, '://') || str_starts_with($url, '/')) ? $url : "";
        }
        $result = [
            'code'      => 0,
            'msg'       => $msg,
            'data'      => $data,
            'url'       => $url,
            'wait'      => $wait,
            '__token__' => '',
        ];
        if ($this->getResponseType() == "html") return view('error', $result);
        return json($result);
    }

    /**
     * @param string $msg
     * @param array $data
     * @param string|null $url
     * @param int $wait
     * @return Response
     */
    public function responseView(string $msg = '操作失败', array $data = [], string $url = null, int $wait = 3): Response
    {
        if (is_null($url)) {
            $url = request()->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url) {
            $url = (strpos($url, '://') || str_starts_with($url, '/')) ? $url : "";
        }
        $result = [
            'code'      => 0,
            'msg'       => $msg,
            'data'      => $data,
            'url'       => $url,
            'wait'      => $wait,
            '__token__' => '',
        ];
        if ($this->getResponseType() == "html") return view('error', $result);
        return json($result);
    }

    /**
     * 获取当前的 response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType(): string
    {
        return (request()->isAjax() || request()->method() == 'POST') ? 'json' : 'html';
    }

}
