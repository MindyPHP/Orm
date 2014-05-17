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

use ReflectionClass;
use Mindy;

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
        if (!$this->isRequired()){
            $this->null = true;
        }
    }

    public function getValue()
    {
        if ($this->value) {
            $mediaUrl = $this->getMediaUrl();
            return ($mediaUrl ? $mediaUrl : '') . $this->value;
        }
        return null;
    }

    public function getDbPrepValue()
    {
        if ($this->value) {
            return $this->value;
        }
        return null;
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
        }elseif(is_string($value) && $value){
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
        $path = null;
        if (isset($data['tmp_name'])) {
            $path = $this->setFile($data['tmp_name'], isset($data['name']) ? $data['name'] : null, self::MODE_POST);
        }
        return $path;
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

        // Make file name
        while (file_exists($uploadFolder . $this->makeFileName($name, $counter))) {
            $counter++;
        }
        $filename = $this->makeFileName($name, $counter);

        // Filename for database
        $file = $this->makeFilePath() . $filename;
        if (!file_exists($uploadFolder)) {
            mkdir($uploadFolder, 0777, true);
        }

        // Filename for upload
        $uploadFile = $uploadFolder . $filename;

        // Move file
        if ($mode == self::MODE_POST) {
            move_uploaded_file($path, $uploadFile);
        } elseif ($mode == self::MODE_LOCAL) {
            rename($path, $uploadFile);
        }

        return $file;
    }

    public function makeFileName($name, $counter = 0)
    {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $ext = ($ext ? '.' . $ext : '');

        if ($this->hashName) {
            $name = md5($name . time());
        } else {
            $name = pathinfo($name, PATHINFO_FILENAME);
        }
        return $name . ($counter > 0 ? '_' . $counter : '') . $ext;
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
        $reflect = new ReflectionClass($this->getModel());
        $short_name = $reflect->getShortName();
        return trim(strtolower(preg_replace('/(?<![A-Z])[A-Z]/', '_\0', $short_name)), '_');
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
