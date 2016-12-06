<?php

namespace Mindy\Component\Template\Adapter;

use RuntimeException;

/**
 * Class FileAdapter
 * @package Mindy\Component\Template
 */
class FileAdapter implements Adapter
{
    /**
     * @var array
     */
    protected $source;

    /**
     * FileAdapter constructor.
     * @param $source
     */
    public function __construct($source)
    {
        if (!is_array($source)) {
            $path = realpath($source);
            if (!$path) {
                throw new RuntimeException(sprintf('source directory %s not found', $source));
            }
            $paths = array($path);
        } else {
            $paths = array();
            foreach ($source as $path) {
                if ($absPath = realpath($path)) {
                    $paths[] = $absPath;
                } else {
                    throw new RuntimeException(sprintf('source directory %s not found', $path));
                }
            }
        }
        $this->source = $paths;
    }

    /**
     * @param $path
     * @return bool
     */
    public function isReadable($path)
    {
        foreach ($this->source as $source) {
            if (is_readable($source . '/' . $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $path
     * @return int|null
     */
    public function lastModified($path)
    {
        foreach ($this->source as $source) {
            if (is_file($source . '/' . $path)) {
                return filemtime($source . '/' . $path);
            }
        }
        return null;
    }

    /**
     * @param $path
     * @return null|string
     */
    public function getContents($path)
    {
        foreach ($this->source as $source) {
            if (is_file($source . '/' . $path)) {
                return file_get_contents($source . '/' . $path);
            }
        }
        return null;
    }
}

