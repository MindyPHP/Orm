<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 20/09/16
 * Time: 15:13
 */

namespace Mindy\Tests\Orm\Image;

use Mindy\Orm\Image\ImageProcessor;

class ImageProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testGeneratePath()
    {
        $file = new \SplFileInfo(__FILE__);
        $processor = new ImageProcessor([
            'basePath' => __DIR__ . '/temp'
        ]);
        $this->assertEquals('ImageProcessorTest_cef4527f9f.php', $processor->generateFilename($file, ['width' => 100]));
        $this->assertEquals('ImageProcessorTest_e0bcc3e26e.php', $processor->generateFilename($file, ['height' => null, 'width' => 100]));
        $this->assertEquals('ImageProcessorTest_e0bcc3e26e.php', $processor->generateFilename($file, ['width' => 100, 'height' => null]));

        $this->assertEquals(__DIR__ . '/temp', dirname($processor->generatePath($file, ['width' => 100])));
    }

    public function testResize()
    {
        $file = __DIR__ . '/cat.jpg';
        $options = [
            'width' => 200,
            'height' => null,
            'uploadTo' => __DIR__ . '/temp',
            'options' => [
                'jpeg_quality' => 100,
                'quality' => 100,
            ]
        ];
        $processor = new ImageProcessor([
            'basePath' => __DIR__ . '/temp',
            'storeOriginal' => false,
            'sizes' => [
                'thumb' => $options
            ]
        ]);

        $processor->process($file);

        $fileInfo = new \SplFileInfo($file);
        $fileName = $processor->generateFilename($fileInfo, $options);
        $filePath = $processor->generatePath($fileInfo, $options);
        $this->assertEquals('cat_12e7b941de.jpg', $fileName);
        $this->assertTrue(is_file(__DIR__ . '/temp/' . $fileName));
        unlink($filePath);

        $this->assertEquals('/cat_12e7b941de.jpg', $processor->url($fileInfo, 'thumb'));
        $this->assertEquals('/cat_12e7b941de.jpg', $processor->url($fileInfo, '200x'));
        $this->assertEquals($filePath, $processor->path($fileInfo, 'thumb'));
    }

    public function testWatermark()
    {
        $file = __DIR__ . '/cat.jpg';
        $options = [
            'width' => 200,
            'height' => null,
            'uploadTo' => __DIR__ . '/temp',
            'options' => [
                'jpeg_quality' => 100,
                'quality' => 100,
            ],
            'watermark' => [
                'file' => __DIR__ . '/watermark.png',
                'position' => 'top'
            ]
        ];
        $processor = new ImageProcessor([
            'basePath' => __DIR__ . '/temp',
            'storeOriginal' => false,
            'sizes' => [
                'thumb' => $options
            ]
        ]);

        $processor->process($file);

        $filePath = __DIR__ . '/temp/cat_821035a60a.jpg';
        $this->assertTrue(is_file($filePath));
        unlink($filePath);
    }
}