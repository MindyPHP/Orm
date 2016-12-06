<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/10/2016
 * Time: 21:26
 */

namespace Mindy\Bundle\TemplateBundle\Tests;

use Mindy\Bundle\TemplateBundle\TemplateFinder\TemplateFinder;
use Mindy\Bundle\TemplateBundle\TemplateFinder\ThemeTemplateFinder;

class TemplateFinderTest extends TestCase
{
    public function testTemplate()
    {
        $finder = new TemplateFinder(__DIR__ . '/app');
        $this->assertEquals([
            __DIR__ . '/app/templates'
        ], $finder->getPaths());
        $this->assertEquals(__DIR__ . '/app/templates/index.html', $finder->find('index.html'));
        $this->assertEquals(__DIR__ . '/app/templates/foo/bar/example.html', $finder->find('foo/bar/example.html'));
    }

    public function testTheme()
    {
        $finder = new ThemeTemplateFinder(__DIR__ . '/app', 'default');
        $this->assertEquals([
            __DIR__ . '/app/themes/default/templates'
        ], $finder->getPaths());
        $this->assertEquals(__DIR__ . '/app/themes/default/templates/index_theme.html', $finder->find('index_theme.html'));
        $this->assertEquals(__DIR__ . '/app/themes/default/templates/page/template/example.html', $finder->find('page/template/example.html'));
    }
}