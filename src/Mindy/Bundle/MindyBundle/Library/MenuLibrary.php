<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 06/10/16
 * Time: 17:09
 */

namespace Mindy\Bundle\MindyBundle\Library;

use Mindy\Bundle\MindyBundle\Model\Menu;
use Mindy\Template\Library;
use Mindy\Template\Renderer;

class MenuLibrary extends Library
{
    protected $template;

    /**
     * MenuLibrary constructor.
     * @param Renderer $template
     */
    public function __construct(Renderer $template)
    {
        $this->template = $template;
    }

    /**
     * @return array
     */
    public function getHelpers()
    {
        return [
            'get_menu' => function ($slug, $template = "menu/menu.html") {
                $menu = Menu::objects()->get(['slug' => $slug]);
                if ($menu === null) {
                    return '';
                }


                return $this->template->render($template, [
                    'items' => $menu->objects()->descendants()->asTree()->all()
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