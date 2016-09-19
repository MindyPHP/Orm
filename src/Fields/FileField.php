<?php

namespace Mindy\Orm\Fields;

use function GuzzleHttp\Psr7\mimetype_from_extension;
use function Mindy\app;
use Mindy\Base\Mindy;
use Mindy\Form\Fields\FileField as FormFileField;
use Mindy\Storage\Files\File;
use Mindy\Storage\Files\LocalFile;
use Mindy\Storage\Files\ResourceFile;
use Mindy\Storage\Files\UploadedFile;
use Mindy\Validation\FileValidator;

/**
 * Class FileField
 * @package Mindy\Orm
 */
class FileField extends CharField
{
    const MODE_LOCAL = 1;
    const MODE_POST = 2;

    /**
     * @var string fs storage name
     */
    public $storageName = 'default';
    /**
     * Upload to template, you can use these variables:
     * %Y - Current year (4 digits)
     * %m - Current month
     * %d - Current day of month
     * %H - Current hour
     * %i - Current minutes
     * %s - Current seconds
     * %O - Current object class (lower-based)
     * @var string
     */
    public $uploadTo = '%M/%O/%Y-%m-%d/';
    /**
     * List of allowed file types
     * @var array|null
     */
    public $types = [];
    /**
     * @var null|int maximum file size or null for unlimited. Default value 2 mb.
     */
    public $maxSize = 2097152;
    /**
     * @var bool convert file name to md5
     */
    public $MD5Name = true;

    /*
    public function init()
    {
        if (!$this->isRequired()) {
            $this->null = true;
        }

        $hasFileValidator = false;
        foreach ($this->validators as $validator) {
            if ($validator instanceof FileValidator) {
                $hasFileValidator = true;
                break;
            }
        }

        if ($hasFileValidator === false) {
            $this->validators = array_merge([
                new FileValidator($this->null, $this->types, $this->maxSize)
            ], $this->validators);
        }
    }
    */

    public function __toString()
    {
        return (string)$this->getUrl();
    }

    /**
     * @return string|null
     */
    public function getUrl()
    {
        if ($this->value) {
            return $this->getStorage()->url(is_array($this->value) ? $this->value['name'] : $this->value);
        }
        return null;
    }

    /**
     * @return \Mindy\Storage\Storage
     */
    public function getStorage()
    {
        return Mindy::app()->storage;
    }

    /**
     * @return \League\Flysystem\Filesystem
     */
    public function getFileSystem()
    {
        return $this->getStorage()->getFileSystem($this->storageName);
    }

    public function getPath()
    {
        $meta = $this->getFileSystem()->getMetadata($this->value);
        return $meta['path'];
    }

    public function getExtension()
    {
        $meta = $this->getFileSystem()->getMetadata($this->value);
        return $meta;
    }

//    public function getValue()
//    {
//        return $this->getUrl();
//    }

    public function delete()
    {
        return $this->getFileSystem()->delete($this->value);
    }

    /**
     * Delete old file from storage (replacing or deleting old file)
     */
    public function deleteOld()
    {
        if ($this->getOldValue()) {
            $fs = $this->getFileSystem();
            if ($fs->has($this->getOldValue())) {
                $fs->delete($this->getOldValue());
            }
        }
    }

    public function getSize()
    {
        return $this->getFileSystem()->getSize($this->value);
    }

    public function getOldValue()
    {
        if ($this->getModel()) {
            return $this->getModel()->getOldAttribute($this->name);
        }
    }

    public function setValue($value)
    {
        if (is_null($value)) {
            $this->deleteOld();
            $this->value = $value;
        } else {
            if (is_array($value) && isset($value['error']) && $value['error'] === UPLOAD_ERR_OK) {
                $this->deleteOld();
                $value = $this->setFile(new UploadedFile($value));
                $this->value = $value;
            } else if (is_string($value) && $value !== $this->value && is_file($value)) {
                $this->deleteOld();
                $value = $this->setFile(new LocalFile($value));
                $this->value = $value;
            } else if (is_string($value) && strpos($value, 'data:') !== false) {
                list($type, $value) = explode(';', $value);
                list(, $value) = explode(',', $value);
                $value = base64_decode($value);
                $this->deleteOld();
                $value = $this->setFile(new ResourceFile($value, null, null, $type));
                $this->value = $value;
            } else if ($value instanceof File) {
                $this->deleteOld();
                $value = $this->setFile($value);
                $this->value = $value;
            }
        }
    }

    public function toArray()
    {
        return $this->value ? [
            'url' => $this->getUrl()
        ] : null;
    }

    /**
     * @param \Mindy\Storage\Files\File $file
     * @param null $name
     * @return null|string
     */
    public function setFile(File $file, $name = null)
    {
        $name = $name ? $name : $file->name;

        if ($this->MD5Name) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $name = md5($name) . '.' . $ext;
        }

        if ($name) {
            // Folder for upload
            $filePath = $this->makeFilePath($name);
            if ($this->getFileSystem()->write($filePath, $file->getContent())) {
                return $filePath;
            }
        }

        return null;
    }

    public function makeFilePath($fileName)
    {
        if (is_callable($this->uploadTo)) {
            $func = $this->uploadTo;
            $uploadTo = $func();
        } else {
            $uploadTo = strtr($this->uploadTo, [
                '%Y' => date('Y'),
                '%m' => date('m'),
                '%d' => date('d'),
                '%H' => date('H'),
                '%i' => date('i'),
                '%s' => date('s'),
                '%O' => $this->getModel()->classNameShort(),
                '%M' => $this->getModel()->getModuleName(),
            ]);
        }
        $fs = $this->getFileSystem();
        if (mb_strlen($fileName, 'UTF-8') > 255) {
            $fileName = mb_substr($fileName, 0, 255, 'UTF-8');
        }
        $path = $uploadTo . $fileName;
        $count = 0;
        if ($fs->has($path)) {
            $meta = $fs->get($path)->getMetadata();
            while ($fs->has($path)) {
                $fileName = strtr("{filename}_{count}.{extension}", [
                    '{filename}' => $meta['filename'],
                    '{extension}' => $meta['extension'],
                    '{count}' => $count += 1
                ]);
                $path = $uploadTo . $fileName;
            }
        }
        return $path;
    }

    public function isValid() : bool
    {
        throw new \Exception('TODO');
        parent::isValid();
        if (isset($this->value['error']) && $this->value['error'] == UPLOAD_ERR_NO_FILE && $this->null == false) {
            $this->addErrors(app()->t('validation', 'Cannot be empty'));
        }
        return $this->hasErrors() === false;
    }

    public function getFormField($form, $fieldClass = null, array $extra = [])
    {
        if (!empty($this->types)) {
            $types = [];
            foreach ($this->types as $type) {
                $types[] = mimetype_from_extension($type);
            }
            $extra = array_merge($extra, [
                'html' => ['accept' => implode('|', $types)]
            ]);
        }
        return parent::getFormField($form, FormFileField::className(), $extra);
    }

    public function onAfterDelete()
    {
        $this->deleteOld();
    }
}
