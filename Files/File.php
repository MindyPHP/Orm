<?php

namespace Mindy\Orm\Files;
use Exception;
use League\Flysystem\Util\MimeType;

/**
 * Class File
 * @package Mindy\Storage
 */
class File extends \SplFileInfo
{
    /**
     * Constructs a new file from the given path.
     *
     * @param string $path The path to the file
     * @param bool $checkPath Whether to check the path or not
     *
     * @throws Exception If the given path is not a file
     */
    public function __construct($path, $checkPath = true)
    {
        if ($checkPath && !is_file($path)) {
            throw new Exception("File not found: " . $path);
        }
        parent::__construct($path);
    }

    /**
     * Returns the mime type of the file.
     *
     * The mime type is guessed using a MimeTypeGuesser instance, which uses finfo(),
     * mime_content_type() and the system binary "file" (in this order), depending on
     * which of those are available.
     *
     * @return string|null The guessed mime type (e.g. "application/pdf")
     *
     * @see MimeTypeGuesser
     */
    public function getMimeType()
    {
        return MimeType::detectByFilename($this->getPathname());
    }

    /**
     * Moves the file to a new location.
     *
     * @param string $directory The destination folder
     * @param string $name The new file name
     *
     * @return File A File object representing the new file
     *
     * @throws Exception if the target file could not be created
     */
    public function move($directory, $name = null)
    {
        $target = $this->getTargetFile($directory, $name);
        if (!@rename($this->getPathname(), $target)) {
            $error = error_get_last();
            throw new Exception(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error['message'])));
        }
        @chmod($target, 0666 & ~umask());
        return $target;
    }

    protected function getTargetFile($directory, $name = null)
    {
        if (!is_dir($directory)) {
            if (false === @mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new Exception(sprintf('Unable to create the "%s" directory', $directory));
            }
        } elseif (!is_writable($directory)) {
            throw new Exception(sprintf('Unable to write in the "%s" directory', $directory));
        }
        $target = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . (null === $name ? $this->getBasename() : $this->getName($name));
        return new self($target, false);
    }

    /**
     * Returns locale independent base name of the given path.
     *
     * @param string $name The new file name
     *
     * @return string containing
     */
    protected function getName($name)
    {
        $originalName = str_replace('\\', '/', $name);
        $pos = strrpos($originalName, '/');
        $originalName = false === $pos ? $originalName : substr($originalName, $pos + 1);
        return $originalName;
    }

    /**
     * Returns the maximum size of an uploaded file as configured in php.ini.
     *
     * @return int The maximum size of an uploaded file in bytes
     */
    public static function getMaxFilesize()
    {
        $iniMax = strtolower(ini_get('upload_max_filesize'));
        if ('' === $iniMax) {
            return PHP_INT_MAX;
        }
        $max = ltrim($iniMax, '+');
        if (0 === strpos($max, '0x')) {
            $max = intval($max, 16);
        } elseif (0 === strpos($max, '0')) {
            $max = intval($max, 8);
        } else {
            $max = (int)$max;
        }
        switch (substr($iniMax, -1)) {
            case 't':
                $max *= 1024;
            case 'g':
                $max *= 1024;
            case 'm':
                $max *= 1024;
            case 'k':
                $max *= 1024;
        }
        return $max;
    }
}
