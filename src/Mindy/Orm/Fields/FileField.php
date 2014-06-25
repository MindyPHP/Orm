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

    public function init()
    {
        if (!$this->isRequired()) {
            $this->null = true;
        }
    }

    /**
     * @return \Modules\Files\Components\Storage
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

    public function setValue($value)
    {
        if (is_null($value)) {
            $this->value = null;
        } elseif (is_array($value) && isset($value['tmp_name']) && $value['tmp_name']) {
            if (is_array($value)) {
                $value = $this->setLoadedFile($value);
            }
            $this->value = $value;
        } elseif (is_string($value) && $value) {
            if ($value == $this->cleanValue) {
                $value = null;
            }
            $this->value = $value;
        }

        return $this->value;
    }

    /**
     * Set data from $_FILES
     * @param array $data
     * @return string $path - path of uploaded file
     */
    public function setLoadedFile(array $data)
    {
        if (isset($data['tmp_name'])) {
            return $this->setFile($data['tmp_name'], isset($data['name']) ? $data['name'] : null, self::MODE_POST);
        }
        return null;
    }

    /**
     * @param $path
     * @param null $name
     * @param int $mode
     * @return null|string
     */
    public function setFile($path, $name = null, $mode = self::MODE_LOCAL)
    {
        $file = null;
        if (!$name) {
            $name = pathinfo($path, PATHINFO_BASENAME);
        }

        // Folder for upload
        $uploadFolder = $this->getMediaPath() . $this->makeFilePath();
        $counter = 0;

        $filename = $this->getStorage()->path($name);
        return $this->getStorage()->save($this->makeFilePath(), file_get_contents($uploadFolder . $filename));
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
                '%O' => $this->revealShortName(),
            ]);
        }
        if (substr($uploadTo, -1) != '/') {
            $uploadTo .= '/';
        }
        return $uploadTo;
    }

    public function revealShortName()
    {
        return trim(strtolower(preg_replace('/(?<![A-Z])[A-Z]/', '_\0', $this->getModel()->shortClassName())), '_');
    }

    public function getMediaPath()
    {
        return Mindy::getPathOfAlias('webroot') . $this->mediaFolder;
    }

    public function getMediaUrl()
    {
        return $this->mediaFolder;
    }
}
