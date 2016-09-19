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
 * @date 03/01/14.01.2014 22:42
 */

namespace Tests\Orm\Fields;

use Mindy\Helper\Alias;
use Mindy\Orm\Fields\ImageField;
use Mindy\Storage\Files\LocalFile;

class ImageFieldTest extends \PHPUnit_Framework_TestCase
{
    public $media;
    public $mock;

    protected function setUp()
    {
        if (\Mindy\Base\Mindy::app() === null) {
            $this->markTestSkipped('Application is not initialized');
        }
        parent::setUp();
        $this->media = Alias::get('www.media');
        $this->mock = Alias::get('www.media') . '/../mock';
    }

    public function testSet()
    {
        $field = new ImageField([
            'uploadTo' => function () {
                return '';
            }
        ]);
        $image = $this->mock . '/lena.png';
        $imageName = $field->setFile(new LocalFile($image));

        $this->assertFileExists($this->media . '/' . $imageName);
    }

    public function testResize()
    {
        $field = new ImageField([
            'uploadTo' => function () {
                return '';
            },
            'sizes' => [
                'preview' => [
                    120, 160,
                    'method' => 'resize'
                ],
                'mini' => [
                    60, 50,
                    'method' => 'adaptiveResize'
                ],
                'big' => [
                    400, 300,
                    'method' => 'adaptiveResize'
                ],
                'watermarked' => [
                    400, 400,
                    'method' => 'adaptiveResize',
                    'watermark' => [
                        'file' => '/mock/watermark.png',
                        'position' => 'repeat'
                    ]
                ],
                'jpg' => [
                    60, 50,
                    'method' => 'adaptiveResize',
                    'format' => 'jpg'
                ],
            ]
        ]);

        $image = $this->mock . '/lena.png';
        $imageName = $field->setFile(new LocalFile($image));

        $this->assertFileExists($this->media . '/' . $imageName);
        $this->assertFileExists($this->media . '/' . $field->sizeStoragePath('mini', $imageName));
        $this->assertFileExists($this->media . '/' . $field->sizeStoragePath('preview', $imageName));
        $this->assertFileExists($this->media . '/' . $field->sizeStoragePath('big', $imageName));
        $this->assertFileExists($this->media . '/' . $field->sizeStoragePath('watermarked', $imageName));
        $this->assertFileExists($this->media . '/' . $field->sizeStoragePath('jpg', $imageName));
    }

    protected function tearDown()
    {
        parent::tearDown();
        $files = glob($this->media . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
