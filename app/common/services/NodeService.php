<?php

namespace app\common\services;

use app\common\services\auth\Node;
use Doctrine\Common\Annotations\AnnotationException;

class NodeService
{

    /**
     * 获取节点服务
     * @return array
     */
    public function getNodeList(): array
    {
        $basePath      = app_path() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'controller';
        $baseNamespace = 'app\admin\controller';
        try {
            $nodeList = (new Node($basePath, $baseNamespace))->getNodeList();
        } catch (AnnotationException | \ReflectionException $e) {
            $nodeList = [];
        }
        return $nodeList;
    }
}
