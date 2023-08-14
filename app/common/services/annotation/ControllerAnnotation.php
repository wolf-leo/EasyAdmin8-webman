<?php

namespace app\common\services\annotation;

use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Class ControllerAnnotation
 *
 * @Annotation
 * @Target("CLASS")
 * @Attributes({
 *     @Attribute("title", type="string"),
 * })
 *
 * @since 2.0
 */
final class ControllerAnnotation
{

    /**
     * Route group prefix for the controller
     *
     * @Required()
     *
     * @var string
     */
    public string $title = '';

    /**
     * 是否开启权限控制
     * @Enum({true,false})
     * @var bool
     */
    public bool $auth = true;

}
