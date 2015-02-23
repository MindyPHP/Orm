<?php

namespace Mindy\Orm\Fields;

use Exception;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Mindy\Orm\Traits\ImageProcess;
use Mindy\Storage\Files\File;
use Mindy\Storage\FileSystemStorage;
use Mindy\Storage\Interfaces\IExternalStorage;
use Mindy\Storage\MimiBoxStorage;

/**
 * Class ImageField
 * @package Mindy\Orm
 */
class ImageField extends FileField
{
    use ImageProcess;

    /**
     * Array with image sizes
     * key 'original' is reserved!
     * example:
     * [
     *      'thumb' => [
     *          300,200,
     *          'method' => 'adaptiveResize'
     *      ]
     * ]
     *
     * There are 3 methods resize(THUMBNAIL_INSET), adaptiveResize(THUMBNAIL_OUTBOUND),
     * adaptiveResizeFromTop(THUMBNAIL_OUTBOUND from top)
     *
     * @var array
     */
    public $sizes = [];

    /**
     * Force resize images
     * @var bool
     */
    public $force = false;

    /**
     * Imagine default options
     * @var array
     */
    public $options = [
        'resolution-units' => ImageInterface::RESOLUTION_PIXELSPERINCH,
        'resolution-x' => 72,
        'resolution-y' => 72,
        'jpeg_quality' => 100,
        'quality' => 100,
        'png_compression_level' => 0
    ];

    /**
     * @var array|null
     *
     * File MUST be described relative to "www" directory!
     *
     * example
     * [
     *  'file' => 'static/images/watermark.png',
     *  'position' => [200,100]
     * ]
     *
     * OR
     *
     * [
     *  'file' => 'static/images/watermark.png',
     *  'position' => 'top'
     * ]
     *
     * position can be array [x,y] coordinates or
     * string with one of available position
     * top, top-left, top-right, bottom, bottom-left, bottom-right, left, right, center
     */
    public $watermark = null;

    /**
     * All supported image types
     * @var array|null
     */
    public $types = ['jpg', 'jpeg', 'png', 'gif'];

    /**
     * Default resize method
     * @var string
     */
    public $defaultResize = 'adaptiveResizeFromTop';

    public $storeOriginal = true;

    public function setFile(File $file, $name = null)
    {
        $name = $name ? $name : $file->name;

        if ($name) {
            $this->value = $this->makeFilePath($name);
            $fileContent = $file->getContent();

            if ($this->getStorage() instanceof FileSystemStorage) {
                try {
                    $image = $this->getImagine()->load($fileContent);
                } catch (Exception $e) {
                    $image = null;
                }
                if ($image) {
                    $fileContent = $this->processSource($image);
                    if ($this->storeOriginal) {
                        $this->value = $this->getStorage()->save($this->value, $fileContent);
                    }
                } else {
                    $this->value = null;
                }
            } elseif ($this->getStorage() instanceof IExternalStorage) {
                $this->value = $this->getStorage()->save($this->value, $fileContent);
            }
        }

        return $this->value;
    }

    public function deleteOld()
    {
        if ($this->getOldValue()) {
            $this->getStorage()->delete($this->getOldValue());
            foreach (array_keys($this->sizes) as $prefix) {
                $this->getStorage()->delete($this->sizeStoragePath($prefix, $this->getOldValue()));
            }
        }
    }

    /**
     * @param $source
     * @param bool $force
     * @return
     */
    public function processSource($source, $force = false)
    {
        $ext = pathinfo($this->value, PATHINFO_EXTENSION);
        foreach ($this->sizes as $prefix => $size) {
            $width = isset($size[0]) ? $size[0] : null;
            $height = isset($size[1]) ? $size[1] : null;
            if (!$width || !$height) {
                list($width, $height) = $this->imageScale($source, $width, $height);
            }
            $method = isset($size['method']) ? $size['method'] : $this->defaultResize;
            $options = isset($size['options']) ? $size['options'] : $this->options;

            $watermark = isset($size['watermark']) ? $size['watermark'] : $this->watermark;
            if (($width || $height) && $method) {
                $newSource = $this->resize($source->copy(), $width, $height, $method);
                if ($watermark) {
                    $newSource = $this->applyWatermark($newSource, $watermark);
                }
                $this->getStorage()->save($this->sizeStoragePath($prefix), $newSource->get($ext, $options), $force);
            }
        }

        if ($this->watermark) {
            $source = $this->applyWatermark($source, $this->watermark);
        }

        return $source->get($ext, $this->options);
    }

    /**
     * @param $prefix
     * @param null $value
     * @return string
     */
    public function sizeStoragePath($prefix = null, $value = null)
    {
        $value = $value ? $value : $this->value;
        $dir = mb_substr_count($value, '/', 'UTF-8') > 0 ? dirname($value) : '';
        // TODO not working with cyrillic
        // $filename = basename($value);
        $filename = ltrim(str_replace($dir, '', $value), '/');

        // TODO ugly, refactor it
        $size = explode('x', $prefix);
        if (strpos($prefix, 'x') !== false && count($size) == 2 && is_numeric($size[0]) && is_numeric($size[1])) {
            $prefix = $this->findSizePrefix($prefix);
        }
        $prefix = $prefix === null ? '' : $this->preparePrefix($prefix);
        return ($dir ? $dir . DIRECTORY_SEPARATOR : '') . $prefix . $filename;
    }

    public function __get($name)
    {
        if (strpos($name, 'url_') === 0) {
            return $this->sizeUrl(str_replace('url_', '', $name));
        } else {
            return parent::__getInternal($name);
        }
    }

    protected function preparePrefix($prefix)
    {
        return rtrim($prefix, '_') . '_';
    }

    /**
     * @param $prefix
     * @return mixed
     */
    public function sizeUrl($prefix)
    {
        if ($this->getStorage() instanceof MimiBoxStorage) {
            $size = explode('x', $prefix);
            if (count($size) > 1) {
                list($width, $height) = $size;
            } else {
                $width = array_pop($size);
                $height = 0;
            }
            $path = $this->sizeStoragePath();
            $path .= "?width=" . $width . '&height=' . $height;
            if ($this->force) {
                $path .= '&force=true';
            }
        } else {
            $path = $this->sizeStoragePath($prefix, false);
            if ($this->force || !is_file($this->getStorage()->path($path))) {
                $absPath = $this->getStorage()->path($this->sizeStoragePath());
                if ($absPath) {
                    $image = $this->getImagine()->open($absPath);
                    $this->processSource($image, true);
                }
            }
        }
        return $this->getStorage()->url($path);
    }

    public function onAfterDelete()
    {
        $this->deleteOld();
    }

    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\ImageField', array $extra = [])
    {
        return parent::getFormField($form, $fieldClass, $extra);
    }

    private function findSizePrefix($prefix, $throw = true)
    {
        $newPrefix = null;
        list($width, $height) = explode('x', trim($prefix, '_'));
        foreach ($this->sizes as $sizePrefix => $size) {
            list($sizeWidth, $sizeHeight) = $size;
            if ($sizeWidth == $width && $sizeHeight == $height) {
                $newPrefix = $sizePrefix;
                break;
            }
        }

        if ($newPrefix === null && $throw) {
            throw new Exception("Prefix with width $width and height $height not found");
        }

        return $newPrefix;
    }
}
