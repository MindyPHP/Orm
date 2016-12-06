<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/10/2016
 * Time: 21:18
 */

namespace Mindy\Bundle\TemplateBundle\TemplateFinder;

/**
 * Interface TemplateFinderInterface
 * @package Mindy\Bundle\TemplateBundle\TemplateFinder
 */
interface TemplateFinderInterface
{
    /**
     * @param $templatePath
     * @return null|string absolute path of template if founded
     */
    public function find($templatePath);

    /**
     * @return array of available template paths
     */
    public function getPaths();
}