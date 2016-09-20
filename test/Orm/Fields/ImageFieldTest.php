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
                return '/fields';
            },
            'sizes' => [
                ['name' => 'test', 'width' => 200]
            ]
        ]);
        $field->setValue(new LocalFile(__DIR__ . '/../Image/cat.jpg'));
        $field->convertToDatabaseValueSQL($field->getValue(), $this->getConnection()->getDatabasePlatform());

        /** @var \League\Flysystem\FilesystemInterface $fs */
        $fs = app()->storage->getFilesystem();
        $this->assertTrue($fs->has('/fields/cat.jpg'));
    }

    protected function tearDown()
    {
        parent::tearDown();
        $files = glob($this->media . '/fields/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
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
            'type' => 'text/php',
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

        @unlink($path);
    }
}
