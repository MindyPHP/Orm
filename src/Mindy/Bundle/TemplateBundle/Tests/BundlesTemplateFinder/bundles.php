<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/10/2016
 * Time: 21:53
 */

return [
    new \Mindy\Bundle\TemplateBundle\TemplateBundle(),
    new \Mindy\Bundle\TemplateBundle\Tests\BundlesTemplateFinder\AppBundle\AppBundle,
    new \Mindy\Bundle\TemplateBundle\Tests\BundlesTemplateFinder\Workspace\FooBundle\FooBundle,
    new \Mindy\Bundle\TemplateBundle\Tests\BundlesTemplateFinder\Workspace\ExampleBundle\ExampleBundle()
];