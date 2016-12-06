<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/10/2016
 * Time: 23:17
 */

namespace Mindy\Bundle\TemplateBundle\Tests;

use Mindy\Component\Template\Library;

class TestLibrary extends Library
{
    /**
     * @return array
     */
    public function getHelpers()
    {
        return [
            'foo' => function () {
                return 'bar';
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