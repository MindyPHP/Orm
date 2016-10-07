<?php

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Exception;
use Mindy\Orm\Files\File;
use Mindy\Orm\Files\LocalFile;
use Mindy\Orm\Files\ResourceFile;
use Mindy\Orm\ModelInterface;
use Mindy\Orm\Traits\FilesystemAwareTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FileField
 * @package Mindy\Orm
 */
class FileField extends CharField
{
    use FilesystemAwareTrait;

    /**
     * Upload to template, you can use these variables:
     * %Y - Current year (4 digits)
     * %m - Current month
     * %d - Current day of month
     * %H - Current hour
     * %i - Current minutes
     * %s - Current seconds
     * %O - Current object class (lower-based)
     * @var string|callable|\Closure
     */
    public $uploadTo = '%M/%O/%Y-%m-%d/';
    /**
     * List of allowed file types
     * @var array|null
     */
    public $mimeTypes = [];
    /**
     * @var null|int maximum file size or null for unlimited. Default value 2 mb.
     */
    public $maxSize = '5M';
    /**
     * @var callable convert file name
     */
    public $nameHasher;
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @return callable|\Closure
     */
    protected function getNameHasher()
    {
        if ($this->nameHasher === null) {
            $this->nameHasher = $this->getDefaultNameHasher();
        }
        return $this->nameHasher;
    }

    /**
     * @return \Closure
     */
    protected function getDefaultNameHasher() : \Closure
    {
        return function ($filePath) {
            $meta = $this->getFilesystem()->getMetadata($filePath);
            return md5($meta['filename']) . '.' . $meta['extension'];
        };
    }

    /**
     * @return array
     */
    public function getValidationConstraints() : array
    {
        $constraints = [];
        if ($this->isRequired() && empty($this->value)) {
            $constraints = [
                new Assert\NotBlank(),
                new Assert\File([
                    'maxSize' => $this->maxSize,
                    'mimeTypes' => $this->mimeTypes,
                ])
            ];
        }

        return $constraints;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->url();
    }

    /**
     * @return string
     */
    public function url() : string
    {
        return $this->getStorage()->url($this->value);
    }

    /**
     * @param array $options
     * @return string
     */
    public function path() : string
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return $this->getFilesystem()->delete($this->value);
    }

    /**
     * @return int
     */
    public function size()
    {
        if (empty($this->value)) {
            return 0;
        }
        if ($this->getFilesystem()->has($this->value)) {
            /** @var \League\Flysystem\File $file */
            $file = $this->getFilesystem()->get($this->value);
            return $file->getSize();
        }
        return 0;
    }

    /**
     * @param \Mindy\Orm\Model|ModelInterface $model
     * @param $value
     */
    public function afterDelete(ModelInterface $model, $value)
    {
        if ($model->hasAttribute($this->getAttributeName())) {
            $fs = $this->getFilesystem();
            if ($fs->has($value)) {
                $fs->delete($value);
            }
        }
    }

    public function setValue($value)
    {
        if (
            is_array($value) &&
            isset($value['error']) &&
            isset($value['tmp_name']) &&
            isset($value['size']) &&
            isset($value['name']) &&
            isset($value['type'])
        ) {

            if ($value['error'] === UPLOAD_ERR_NO_FILE) {
                $value = null;
            } else {
                $value = new UploadedFile(
                    $value['tmp_name'],
                    $value['name'],
                    $value['type'],
                    (int)$value['size'],
                    (int)$value['error']
                );
            }

        } else if (is_string($value)) {
            if (strpos($value, 'data:') !== false) {
                list($type, $value) = explode(';', $value);
                list(, $value) = explode(',', $value);
                $value = base64_decode($value);
                $value = new ResourceFile($value, null, null, $type);
            } else if (realpath($value)) {
                $value = new LocalFile(realpath($value));
            }
        }

        if ($value === null) {
            $this->value = null;
        } else if ($value instanceof File || $value instanceof UploadedFile) {
            $this->value = $value;
        }
    }

    /**
     * @return array|null
     */
    public function toArray()
    {
        return empty($this->value) ? null : ['url' => $this->url()];
    }

    public function __get($name)
    {
        if ($name === 'url') {
            return $this->url();
        } else if ($name === 'value') {
            return $this->getValue();
        } else if (property_exists($this, $name)) {
            return $this->{$name};
        } else {
            throw new Exception('Unknown property');
        }
    }

    /**
     * @return string
     */
    protected function getUploadTo() : string
    {
        if (is_callable($this->uploadTo)) {
            return $this->uploadTo->__invoke();
        } else {
            $model = $this->getModel();
            return strtr($this->uploadTo, [
                '%Y' => date('Y'),
                '%m' => date('m'),
                '%d' => date('d'),
                '%H' => date('H'),
                '%i' => date('i'),
                '%s' => date('s'),
                '%O' => $model->classNameShort(),
                '%M' => $model->getModuleName(),
            ]);
        }
    }

    /**
     * @param string $fieldClass
     * @return false|null|string
     */
    public function getFormField($fieldClass = '\Mindy\Forms\Fields\FileField')
    {
        return parent::getFormField($fieldClass);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof UploadedFile) {
            $value = $this->saveUploadedFile($value);
        } else if ($value instanceof File) {
            $value = $this->saveFile($value);
        }

        if (is_string($value)) {
            $value = $this->normalizeValue($value);
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    public function convertToPHPValueSQL($value, AbstractPlatform $platform)
    {
        $this->value = $value;
        return $this;
    }

    protected function normalizeValue(string $value)
    {
        return str_replace('//', '/', $value);
    }

    protected function saveUploadedFile(UploadedFile $file)
    {
        $path = $this->getUploadTo() . DIRECTORY_SEPARATOR;

        $fs = $this->getFilesystem();
        if ($fs->has($path . DIRECTORY_SEPARATOR . $file->getClientOriginalName())) {
            $fs->delete($path . DIRECTORY_SEPARATOR . $file->getClientOriginalName());
        }
        if (!$fs->write($path . DIRECTORY_SEPARATOR . $file->getClientOriginalName(), file_get_contents($file->getRealPath()))) {
            throw new Exception('Failed to save file');
        }

        return $path . DIRECTORY_SEPARATOR . $file->getClientOriginalName();
    }

    protected function saveFile(File $file)
    {
        $contents = file_get_contents($file->getRealPath());
        $value = $this->getUploadTo() . DIRECTORY_SEPARATOR . $file->getFilename();
        $fs = $this->getFilesystem();
        if ($fs->has($value)) {
            $fs->delete($value);
        }

        if (!$fs->write($value, $contents)) {
            throw new Exception('Failed to save file');
        }

        return $value;
    }
}
