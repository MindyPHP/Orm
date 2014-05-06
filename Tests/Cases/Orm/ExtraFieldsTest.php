<?php
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
 * @date 04/01/14.01.2014 00:53
 */

namespace Tests\Orm;


use Mindy\Orm\Fields\MarkdownField;
use Mindy\Orm\Fields\MarkdownHtmlField;
use Tests\DatabaseTestCase;
use Tests\Models\MarkdownModel;


class ExtraFieldsTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new MarkdownModel]);
    }

    public function tearDown()
    {
        $this->dropModels([new MarkdownModel]);
    }

    public function testExtraFields()
    {
        $model = new MarkdownModel();

        $fields = $model->getFieldsInit();
        $this->assertEquals(3, count($fields));

        $this->assertInstanceOf(MarkdownField::className(), $model->getField('content'));
        $this->assertInstanceOf(MarkdownHtmlField::className(), $model->getField('content_html'));

        $model->content = "# Hello world";
        $this->assertEquals("<h1>Hello world</h1>\n", $model->content_html);
        $model->save();

        $fetchModel = MarkdownModel::objects()->filter(['pk' => 1])->get();
        $this->assertEquals("# Hello world", $fetchModel->content);
        $this->assertEquals("<h1>Hello world</h1>\n", $fetchModel->content_html);
    }
}
