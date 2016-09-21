<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 20/09/16
 * Time: 15:13
 */

namespace Mindy\Tests\Orm\Image;

use function Mindy\app;
use Mindy\Orm\Image\ImageProcessor;

class ImageProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        /** @var \League\Flysystem\FilesystemInterface $fs */
        $fs = app()->storage->getFilesystem();
        foreach ($fs->listContents('/temp') as $file) {
            $fs->delete($file['path']);
        }
    }

    public function testGeneratePath()
    {
        $file = __FILE__;
        $processor = new ImageProcessor();
        $this->assertEquals('ImageProcessorTest_cef4527f9f.php', basename($processor->generateFilename($file, ['width' => 100])));
        $this->assertEquals('ImageProcessorTest_e0bcc3e26e.php', basename($processor->generateFilename($file, ['height' => null, 'width' => 100])));
        $this->assertEquals('ImageProcessorTest_e0bcc3e26e.php', basename($processor->generateFilename($file, ['width' => 100, 'height' => null])));
    }

    public function testResize()
    {
        $options = [
            'name' => 'thumb',
            'width' => 200,
            'height' => null,
            'options' => [
                'jpeg_quality' => 100,
                'quality' => 100,
            ]
        ];
        $processor = new ImageProcessor([
            'uploadTo' => '/temp',
            'storeOriginal' => false,
            'sizes' => [
                $options
            ]
        ]);

        $fileName = $processor->generateFilename('/temp/cat.jpg', $options);
        $this->assertEquals('cat_0343c5aa39.jpg', basename($fileName));

        $processor->process(__DIR__ . '/cat.jpg');

        /** @var \League\Flysystem\FilesystemInterface $fs */
        $fs = app()->storage->getFilesystem();
        $this->assertEquals(1, count($fs->listContents('/temp/')));
        $this->assertEquals('/media/temp/cat_0343c5aa39.jpg', $processor->url('/temp/cat.jpg', ['name' => 'thumb']));
        $this->assertEquals('/media/temp/cat_0343c5aa39.jpg', $processor->url('/temp/cat.jpg', ['width' => 200]));
    }

    public function testWatermark()
    {
        $options = [
            'name' => 'thumb',
            'width' => 200,
            'height' => null,
            'options' => [
                'jpeg_quality' => 100,
                'quality' => 100,
            ],
            'watermark' => [
                'file' => __DIR__ . '/watermark.png',
                'position' => 'repeat'
            ]
        ];
        $processor = new ImageProcessor([
            'uploadTo' => '/temp',
            'storeOriginal' => false,
            'sizes' => [
                $options
            ]
        ]);

        $processor->process(__DIR__ . '/cat.jpg');

        /** @var \League\Flysystem\FilesystemInterface $fs */
        $fs = app()->storage->getFilesystem();
        $this->assertEquals(1, count($fs->listContents('/temp/')));
    }
}
