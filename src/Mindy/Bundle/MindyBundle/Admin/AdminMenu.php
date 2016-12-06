<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 07/10/16
 * Time: 14:58
 */

namespace Mindy\Bundle\MindyBundle\Admin;

/**
 * Class AdminMenu
 * @package Mindy\Bundle\MindyBundle\Admin
 */
class AdminMenu
{
    /**
     * @var array
     */
    protected $menu = [];

    /**
     * AdminMenu constructor.
     * @param array $menu
     */
    public function __construct(array $menu = [])
    {
        $this->menu = $menu;
    }

    /**
     * @return array
     */
    public function getMenu()
    {
        // todo add check permissions
        return $this->menu;
    }
}