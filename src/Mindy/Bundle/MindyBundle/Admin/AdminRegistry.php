<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 14/11/2016
 * Time: 19:57
 */

namespace Mindy\Bundle\MindyBundle\Admin;

class AdminRegistry
{
    protected $controllers = [];

    public function addAdmin($id, $slug)
    {
        $this->controllers[$slug] = $id;
    }

    public function resolveAdmin($slug)
    {
        if (isset($this->controllers[$slug])) {
            return $this->controllers[$slug];
        }

        return null;
    }
}