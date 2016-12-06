<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 07/10/16
 * Time: 15:48
 */

namespace Mindy\Bundle\MindyBundle\Library;

use Mindy\Template\Library;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class MetaLibrary extends Library implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return array
     */
    public function getHelpers()
    {
        return [
            'render_meta' => function ($template = 'meta/meta.html') {
                $request = $this->container->get('request_stack')->getCurrentRequest();
                $data = $this->container->get('meta_provider')->process($request);
                return $this->container->get('template')->render($template, $data);
            }
        ];
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return [];
    }
}