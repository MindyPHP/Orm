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


use Mindy\Base\Mindy;
use Mindy\Helper\Alias;
use Mindy\Orm\Fields\ImageField;
use Mindy\Storage\Files\LocalFile;
use Mindy\Tests\TestCase;

class ImageFieldTest extends TestCase
{
    public $media;

    protected function setUp()
    {
        parent::setUp();
        $this->media = Mindy::app()->basePath . '/media';
        Alias::set('www', Mindy::app()->basePath);
    }

    public function testSet()
    {
        $field = new ImageField([
            'uploadTo' => function(){
                return '';
            }
        ]);
        $image = Mindy::app()->basePath . '/mock/lena.png';
        $imageName = $field->setFile(new LocalFile($image));

        $this->assertFileExists($this->media . '/' . $imageName);
    }

    public function testResize()
    {
        $field = new ImageField([
            'uploadTo' => function(){
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
                ]
            ]
        ]);

        $image = Mindy::app()->basePath . '/mock/lena.png';
        $imageName = $field->setFile(new LocalFile($image));

        $this->assertFileExists($this->media . '/' . $imageName);
        $this->assertFileExists($this->media . '/' . $field->sizeStoragePath('mini', $imageName));
        $this->assertFileExists($this->media . '/' . $field->sizeStoragePath('preview', $imageName));
        $this->assertFileExists($this->media . '/' . $field->sizeStoragePath('big', $imageName));
        $this->assertFileExists($this->media . '/' . $field->sizeStoragePath('watermarked', $imageName));
    }

    protected function tearDown()
    {
        parent::tearDown();
        $files = glob($this->media . '/*');
        foreach($files as $file){
            if(is_file($file))
                unlink($file);
        }
    }
}
