<?php

namespace Mindy\Component\Template\Tests;

use Mindy\Component\Template\Loader;
use Mindy\Component\Template\Renderer;

/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 01/08/14.08.2014 13:51
 */
class BaseTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        foreach (glob(__DIR__ . '/cache/*.php') as $file) {
            unlink($file);
        }
    }

    protected function getTemplate()
    {
        return new Loader([
            'source' => __DIR__ . '/templates',
            'target' => __DIR__ . '/cache',
            'mode' => Loader::RECOMPILE_ALWAYS
        ]);
    }

    public function providerLoadFromString()
    {
        return [
            ['hello {{ world }}', 'hello world', ['world' => 'world']],
            ['hello {{ world }}', 'hello ', []],
            ['{% block content %}hello {{ world }}{% endblock %}', 'hello world', ['world' => 'world']],

            ['{% extends "base.html" %}', '1', []],
            ['{% extends "base.html" %}{% block content %}2{% endblock %}', '2', []],
        ];
    }

    /**
     * @dataProvider providerLoadFromString
     */
    public function testLoadFromString($template, $result, $data)
    {
        $tpl = $this->getTemplate()->loadFromString($template);
        $this->assertEquals($tpl->render($data), $result);
    }

    public function providerLoad()
    {
        return [
            ['main.html', '1', []],
            ['main.html', '12', ['data' => 2]],
            ['global_variable.html', '1', ['global_variable' => 1]],
            ['loop.html', '123456', ['data' => [
                [1, 2, 3],
                [4, 5, 6]
            ]]],
        ];
    }

    /**
     * @dataProvider providerLoad
     */
    public function testLoad($template, $result, $data)
    {
        $tpl = $this->getTemplate()->load($template);
        $this->assertEquals($tpl->render($data), $result);
    }

    public function providerTemplate()
    {
        return [
            ['{{ a }}', ['a' => 'b'], 'b'],
            // Concat
            ['{{ a ~ b }}', ['a' => 'a', 'b' => 'b'], 'ab'],
            // Cycles
            ['{% for i in data %}{{ i }}{% endfor %}', ['data' => [1, 2, 3]], '123'],
            ['{% for t, i in data %}{% if t > 1 %}{% break %}{% endif %}{{ i }}{% endfor %}', ['data' => [1, 2, 3]], '12'],
            // Cycles loop helper
            ['{% for i in data %}{{ loop.counter }}{% endfor %}', ['data' => [1, 2, 3]], '123'],
            ['{% for i in data %}{{ loop.counter0 }}{% endfor %}', ['data' => [1, 2, 3]], '012'],
            ['{% for i in data %}{{ forloop.counter }}{% endfor %}', ['data' => [1, 2, 3]], '123'],
            ['{% for i in data %}{{ forloop.counter0 }}{% endfor %}', ['data' => [1, 2, 3]], '012'],
            // Math
            ['{{ a / b }}', ['a' => 10, 'b' => 2], '5'],
            ['{{ a * b }}', ['a' => 10, 'b' => 2], '20'],
            ['{{ a + b }}', ['a' => 10, 'b' => 2], '12'],
            ['{{ a - b }}', ['a' => 10, 'b' => 2], '8'],
            ['{{ a % b }}', ['a' => 10, 'b' => 2], '0'],
        ];
    }

    /**
     * @dataProvider providerTemplate
     * @param $template
     * @param array $data
     * @param $result
     */
    public function testTemplate($template, array $data, $result)
    {
        $tpl = $this->getTemplate()->loadFromString($template);
        $this->assertEquals($tpl->render($data), $result);
    }

    public function testRenderer()
    {
        $renderer = new Renderer([
            'source' => function () {
                $templates = [__DIR__ . '/templates'];
                $modulesTemplates = glob(__DIR__ . '/Modules/*/templates');
                $themesTemplates = glob(__DIR__ . '/themes/*/templates');

                return array_merge($templates, $modulesTemplates, $themesTemplates);
            },
            'target' => __DIR__ . '/cache',
            'mode' => Renderer::RECOMPILE_ALWAYS
        ]);
        $this->assertEquals('foobar', $renderer->render('example.html', ['data' => 'foobar']));
        $this->assertEquals('foobar', $renderer->render('core/index.html', ['data' => 'foobar']));
        $this->assertEquals('foobar', $renderer->renderString('{{ data }}', ['data' => 'foobar']));
        $this->assertInstanceOf(Renderer::class, $renderer->compile('core/index.html', ['data' => 'foobar']));

        $this->assertTrue($renderer->isValid('core/index.html', $error));
    }
}
