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
                'thumb' => $options
            ]
        ]);

        $fileName = $processor->generateFilename('/temp/cat.jpg', $options);

        $processor->process(__DIR__ . '/../app/media/cat.jpg');

        $this->assertEquals('cat_d434de1940.jpg', basename($fileName));

        /** @var \League\Flysystem\FilesystemInterface $fs */
        $fs = app()->storage->getFilesystem();
        $this->assertTrue($fs->has('/temp/cat_d434de1940.jpg'));
        $this->assertEquals('/media/temp/cat_40cd750bba.jpg', $processor->url('/temp/cat.jpg', 'thumb'));
        $this->assertEquals('/media/temp/cat_40cd750bba.jpg', $processor->url('/temp/cat.jpg', '200x'));
    }

    public function testWatermark()
    {
        $options = [
            'width' => 200,
            'height' => null,
            'uploadTo' => __DIR__ . '/temp',
            'options' => [
                'jpeg_quality' => 100,
                'quality' => 100,
            ],
            'watermark' => [
                'file' => '/watermark.png',
                'position' => 'repeat'
            ]
        ];
        $processor = new ImageProcessor([
            'uploadTo' => '/temp',
            'storeOriginal' => false,
            'sizes' => [
                'thumb' => $options
            ]
        ]);

        $processor->process(__DIR__ . '/../app/media/cat.jpg');

        /** @var \League\Flysystem\FilesystemInterface $fs */
        $fs = app()->storage->getFilesystem();
        $this->assertTrue($fs->has('/temp/cat_2ebd697cce.jpg'));
    }
}