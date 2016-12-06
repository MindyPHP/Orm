<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 14/11/2016
 * Time: 20:29
 */

namespace Mindy\Bundle\MindyBundle\Dashboard;

use Mindy\Template\Renderer;

class Dashboard
{
    /**
     * @var array
     */
    protected $widgets = [];

    /**
     * @var Renderer
     */
    protected $template;

    /**
     * Dashboard constructor.
     * @param Renderer $template
     */
    public function __construct(Renderer $template)
    {
        $this->template = $template;
    }

    /**
     * @param WidgetInterface $widget
     */
    public function addWidget(WidgetInterface $widget)
    {
        $this->widgets[] = $widget;
    }

    /**
     * @return array
     */
    public function getWidgets()
    {
        return array_map(function ($widget) {
            return $this->template->render($widget->getTemplate(), $widget->getData());
        }, $this->widgets);
    }
}