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

use League\Flysystem\Util\MimeType;
use function Mindy\app;
use Mindy\Orm\Fields\FileField;
use Mindy\Orm\Fields\ImageField;
use Mindy\Orm\Files\ResourceFile;
use Mindy\Orm\Files\UploadedFile;
use Mindy\Orm\Files\LocalFile;
use Mindy\Tests\Orm\Models\User;
use Mindy\Tests\Orm\OrmDatabaseTestCase;

class ImageFieldTest extends OrmDatabaseTestCase
{
    public $media;
    public $mock;

    public function setUp()
    {
        parent::setUp();

        $this->app = app();
        $this->media = realpath(__DIR__ . '/../app/media');
        $this->mock = realpath(__DIR__ . '/../app/mock');
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

    public function testFileField()
    {
        $model = new User;

        $file = new FileField();
        $file->setModel($model);
        $file->setFile(new LocalFile(__FILE__));

        $content = $this->app->storage->getFilesystem()->has('qwe');
    }

    public function testFileFieldValidation()
    {
        $mediaPath = realpath(__DIR__ . '/../app/media');
        $model = new User;

        $field = new FileField([
            'name' => 'file',
            'required' => true,
            'uploadTo' => $mediaPath
        ]);
        $field->setModel($model);
        $this->assertFalse($field->isValid());
        $this->assertEquals(['This value should not be blank.'], $field->getErrors());

        $path = __DIR__ . '/test.txt';
        file_put_contents($path, '123');
        $file = [
            'name' => 'Test.php',
            'type' => MimeType::detectByFilename($path),
            'tmp_name' => $path,
            'error' => UPLOAD_ERR_OK,
            'size' => 10000000
        ];
        $uploadedFile = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['size'], $file['error']);
        $field->setValue($uploadedFile);
        $this->assertFalse($field->isValid());
        $this->assertEquals(['The file could not be uploaded.'], $field->getErrors());

        $field = new FileField([
            'mimeTypes' => [
                'image/*'
            ],
            'name' => 'file',
            'required' => true,
            'uploadTo' => $mediaPath,
        ]);
        $field->setModel($model);

        $uploadedFile = new LocalFile('qweqwe', false);
        $field->setValue($uploadedFile);
        $this->assertFalse($field->isValid());
        $this->assertEquals('The file could not be found.', $field->getErrors()[0]);

        $uploadedFile = new ResourceFile(base64_encode(file_get_contents(__FILE__)));
        $field->setValue($uploadedFile);
        $this->assertFalse($field->isValid());
        $this->assertEquals('The mime type of the file is invalid ("text/plain"). Allowed mime types are "image/*".', $field->getErrors()[0]);
    }
}
