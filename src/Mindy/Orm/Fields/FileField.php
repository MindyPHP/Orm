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
 * @date 17/04/14.04.2014 17:36
 */

namespace Mindy\Orm\Fields;

use Mindy\Base\Mindy;
use Mindy\Helper\Alias;
use Mindy\Validation\FileValidator;
use Mindy\Storage\Files\File;
use Mindy\Storage\Files\LocalFile;
use Mindy\Storage\Files\UploadedFile;
use Mindy\Form\Fields\FileField as FormFileField;

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

    public $hashName = true;

    public $cleanValue = '';
    /**
     * List of allowed file types
     * @var array|null
     */
    public $types = null;

    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->validators = array_merge([
            new FileValidator($this->null, $this->types)
        ], $this->validators);
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

    public function init()
    {
        if (!$this->isRequired()) {
            $this->null = true;
        }
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
        return $this->getModel()->getOldAttribute($this->name);
    }

    public function setValue($value)
    {
        if (is_null($value)) {
            $this->deleteOld();
        } else {
            if (is_array($value)) {
                $this->deleteOld();
                $value = $this->setFile(new UploadedFile($value));
            } else if (is_string($value) && is_file($value)) {
                $this->deleteOld();
                $value = $this->setFile(new LocalFile($value));
            }
        }
        $this->value = $value;
    }

    /**
     * @param \Mindy\Storage\Files\File $file
     * @param null $name
     * @return null|string
     */
    public function setFile(File $file, $name = null)
    {
        $name = $name ? $name : $file->name;

        if ($name) {
            // Folder for upload
            $filePath = $this->makeFilePath($name);
            if ($this->getStorage()->save($filePath, file_get_contents($file->path))) {
                return $filePath;
            }
        }

        return null;
    }

    public function makeFilePath($fileName = '')
    {
        if (is_callable($this->uploadTo)) {
            $uploadTo = $this->uploadTo();
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
        return rtrim($uploadTo, '/') . '/' . ($fileName ? $fileName : '');
    }

    public function isValid()
    {
        parent::isValid();
        if(isset($this->value['error']) && $this->value['error'] == UPLOAD_ERR_NO_FILE && $this->null == false) {
            $this->addErrors([$this->name . ' cannot be empty']);
        }
        return $this->hasErrors() === false;
    }

    public function getFormField($form, $fieldClass = null)
    {
        return parent::getFormField($form, FormFileField::className());
    }
}
