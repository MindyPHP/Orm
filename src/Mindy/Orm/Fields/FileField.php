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
use Mindy\Storage\Files\File;
use Mindy\Storage\Files\LocalFile;
use Mindy\Storage\Files\UploadedFile;

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
    public $uploadTo = '/models/%O/%Y-%m-%d/';

    public $mediaFolder = '/public';

    public $hashName = true;

    public $cleanValue = 'NULL';

    public function __toString()
    {
        return (string)$this->getValue();
    }

    public function getUrl()
    {
        return $this->getValue();
    }

    public function init()
    {
        if (!$this->isRequired()) {
            $this->null = true;
        }
    }

    public function setValue($value)
    {
        if (is_null($value)) {
            $this->getStorage()->delete($this->getValue());
        } else {
            if(is_array($value)) {
                $value = $this->setFile(new UploadedFile($value));
            } else if(is_string($value) && is_file($value)) {
                $value = $this->setFile(new LocalFile($value));
            }
        }
        return parent::setValue($value);
    }

    /**
     * @return \Mindy\Storage\Storage
     */
    public function getStorage()
    {
        return Mindy::app()->storage;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getDbPrepValue()
    {
        return $this->value;
    }

    public function getPath()
    {
        return $this->getStorage()->path($this->getCleanValue());
    }

    public function getCleanValue()
    {
        return substr($this->getValue(), strlen($this->getMediaUrl()));
    }

    public function delete()
    {
        return $this->getStorage()->delete($this->getValue());
    }

    public function getSize()
    {
        return $this->getStorage()->size($this->getCleanValue());
    }

    /**
     * @param \Mindy\Storage\Files\File $file
     * @param null $name
     * @return null|string
     */
    public function setFile(File $file, $name = null)
    {
        $name = $name ? $name : $file->name;

        // Folder for upload
        $filePath = $this->makeFilePath();
        $uploadFolder = $this->getMediaPath() . $filePath;
        if ($this->getStorage()->save($uploadFolder . $name, file_get_contents($file->path))) {
            return $this->getMediaUrl() . $filePath . $name;
        } else {
            return null;
        }
    }

    public function makeFilePath()
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
                '%O' => $this->getModel()->shortClassName(),
                '%M' => $this->getModel()->getModuleName(),
            ]);
        }
        return rtrim($uploadTo, '/') . '/';
    }

    public function getMediaPath()
    {
        return Alias::get('www') . $this->mediaFolder;
    }

    public function getMediaUrl()
    {
        return $this->mediaFolder;
    }
}
