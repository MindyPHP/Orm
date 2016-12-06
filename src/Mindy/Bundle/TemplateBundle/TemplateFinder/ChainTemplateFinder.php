<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/10/2016
 * Time: 21:21
 */

namespace Mindy\Bundle\TemplateBundle\TemplateFinder;

/**
 * Class ChainTemplateFinder
 * @package Mindy\Bundle\TemplateBundle\TemplateFinder
 */
class ChainTemplateFinder implements TemplateFinderInterface
{
    /**
     * @var TemplateFinderInterface[]
     */
    protected $finders = [];

    /**
     * Finder constructor.
     * @param array $finders
     */
    public function __construct(array $finders = [])
    {
        $this->finders = $finders;
    }

    /**
     * @param TemplateFinderInterface $finder
     */
    public function addFinder(TemplateFinderInterface $finder)
    {
        $this->finders[] = $finder;
    }

    /**
     * @param $templatePath
     * @return mixed
     */
    public function find($templatePath)
    {
        $templates = [];
        foreach ($this->finders as $finder) {
            $template = $finder->find($templatePath);
            if ($template !== null) {
                $templates[] = $template;
            }
        }
        return array_shift($templates);
    }

    /**
     * @return array of string
     */
    public function getPaths()
    {
        $paths = [];
        foreach ($this->finders as $finder) {
            $paths = array_merge($paths, $finder->getPaths());
        }
        return $paths;
    }
}