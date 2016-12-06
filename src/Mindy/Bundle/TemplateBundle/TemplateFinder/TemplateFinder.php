<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/10/2016
 * Time: 21:19
 */

namespace Mindy\Bundle\TemplateBundle\TemplateFinder;

class TemplateFinder implements TemplateFinderInterface
{
    protected $basePath;
    protected $templatesDir;

    /**
     * TemplateFinder constructor.
     * @param $basePath
     * @param string $templatesDir
     */
    public function __construct($basePath, $templatesDir = 'templates')
    {
        $this->basePath = $basePath;
        $this->templatesDir = $templatesDir;
    }

    /**
     * @param $templatePath
     * @return null|string absolute path of template if founded
     */
    public function find($templatePath)
    {
        $path = implode(DIRECTORY_SEPARATOR, [$this->basePath, $this->templatesDir, $templatePath]);
        if (is_file($path)) {
            return $path;
        }

        return null;
    }

    /**
     * @return array of available template paths
     */
    public function getPaths()
    {
        return [
            implode(DIRECTORY_SEPARATOR, [$this->basePath, $this->templatesDir])
        ];
    }
}