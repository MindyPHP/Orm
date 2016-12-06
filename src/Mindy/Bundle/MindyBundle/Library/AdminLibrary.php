<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 30/11/16
 * Time: 18:30
 */

namespace Mindy\Bundle\MindyBundle\Library;

use Mindy\Bundle\MindyBundle\Admin\AdminMenu;
use Mindy\Template\Library;
use Mindy\Template\Renderer;

class AdminLibrary extends Library
{
    protected $adminMenu;
    protected $renderer;

    public function __construct(AdminMenu $adminMenu, Renderer $renderer)
    {
        $this->adminMenu = $adminMenu;
        $this->renderer = $renderer;
    }

    /**
     * @return array
     */
    public function getHelpers()
    {
        return [
            'admin_menu' => function ($template = 'admin/_menu.html') {
                return $this->renderer->render($template, [
                    'adminMenu' => $this->adminMenu->getMenu()
                ]);
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