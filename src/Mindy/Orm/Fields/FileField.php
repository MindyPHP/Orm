<?php

namespace Mindy\Orm\Fields;

use Mindy\Base\Mindy;
use Mindy\Form\Fields\FileField as FormFileField;
use Mindy\Helper\File as FileHelper;
use Mindy\Locale\Translate;
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

    public function getPath()
    {
        return $this->getStorage()->path($this->value);
    }

    public function getExtension()
    {
        return $this->getStorage()->extension($this->value);
    }

//    public function getValue()
//    {
//        return $this->getUrl();
//    }

    public function delete()
    {
        return $this->getStorage()->delete($this->value);
    }

    /**
     * Delete old file from storage (replacing or deleting old file)
     */
    public function deleteOld()
    {
        if ($this->getOldValue()) {
            $this->getStorage()->delete($this->getOldValue());
        }
    }

    public function getSize()
    {
        return $this->getStorage()->size($this->value);
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
            $name = md5(str_replace("." . $ext, "", $name)) . '.' . $ext;
        }

        if ($name) {
            // Folder for upload
            $filePath = $this->makeFilePath($name);
            if ($filePath = $this->getStorage()->save($filePath, $file->getContent())) {
                return $filePath;
            }
        }

        return null;
    }

    public function makeFilePath($fileName = '')
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
        $uploadTo = rtrim($uploadTo, '/');
        return ($uploadTo ? $uploadTo . '/' : '') . ($fileName ? $fileName : '');
    }

    public function isValid()
    {
        parent::isValid();
        if (isset($this->value['error']) && $this->value['error'] == UPLOAD_ERR_NO_FILE && $this->null == false) {
            $this->addErrors(Translate::getInstance()->t('validation', 'Cannot be empty'));
        }
        return $this->hasErrors() === false;
    }

    public function getFormField($form, $fieldClass = null, array $extra = [])
    {
        if (!empty($this->types)) {
            $types = [];
            foreach ($this->types as $type) {
                $types[] = FileHelper::getMimeTypeByExtension($type);
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
