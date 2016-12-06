<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 07/10/16
 * Time: 15:51
 */

namespace Mindy\Bundle\MindyBundle\Provider;

use Mindy\Bundle\MindyBundle\Model\Meta;
use Symfony\Component\HttpFoundation\Request;

class MetaProvider
{
    /**
     * @param Request $request
     * @return array
     */
    public function process(Request $request) : array
    {
        $meta = Meta::objects()->asArray()->get([
            'domain' => $request->getHost(),
            'url' => $request->getPathInfo()
        ]);

        if ($meta === null) {
            $meta = [];
        }

        return ['meta' => $meta];
    }
}