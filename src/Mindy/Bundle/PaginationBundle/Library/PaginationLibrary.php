<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 06/12/16
 * Time: 12:02
 */

namespace Mindy\Bundle\PaginationBundle\Library;

use Mindy\Component\Pagination\PaginationView;
use Mindy\Component\Template\Library;
use Mindy\Component\Template\Renderer;

class PaginationLibrary extends Library
{
    /**
     * @var Renderer
     */
    protected $template;

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
            'pagination_render' => function (PaginationView $view, $template = 'pagination/default.html') {
                return $this->template->render($template, [
                    'pager' => $view
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