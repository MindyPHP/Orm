<?php

namespace Mindy\Orm\Fields;

use Exception;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Mindy\Base\Mindy;
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

    protected $availableResizeMethods = [
        'resize', 'adaptiveResize', 'adaptiveResizeFromTop'
    ];
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
     * top, top-left, top-right, bottom, bottom-left, bottom-right, left, right, center, repeat
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
    /**
     * @var bool
     */
    public $storeOriginal = true;
    /**
     * Recreate file if missing
     * @var bool
     */
    public $checkMissing = false;
    /**
     * Cached original
     * @var null | \Imagine\Image\ImagineInterface
     */
    public $_original = null;
    /**
     * Cached original name
     * @var null | string
     */
    public $_originalName = null;

    public function setFile(File $file, $name = null)
    {
        $name = $name ? $name : $file->name;

        if ($this->MD5Name) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $name = md5(str_replace("." . $ext, "", $name)) . '.' . $ext;
        }

        if ($name) {
            $this->value = $this->makeFilePath($name);
            $fileContent = $file->getContent();

            if ($this->getStorage() instanceof FileSystemStorage) {
                try {
                    $image = $this->getImagine()->load($fileContent);
                } catch (Exception $e) {
                    Mindy::app()->logger->error($e->getMessage(), [
                        'line' => $e->getLine(),
                    ]);
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
     * @param array|null $onlySizes - Resize and save only sizes, described in this array
     * @return
     */
    public function processSource($source, $force = false, $onlySizes = null)
    {
        $ext = pathinfo($this->value, PATHINFO_EXTENSION);
        foreach ($this->sizes as $prefix => $size) {
            if (is_array($onlySizes) && !in_array($prefix, $onlySizes)) {
                continue;
            }
            $width = isset($size[0]) ? $size[0] : null;
            $height = isset($size[1]) ? $size[1] : null;
            if (!$width || !$height) {
                list($width, $height) = $this->imageScale($source, $width, $height);
            }
            $method = isset($size['method']) ? $size['method'] : $this->defaultResize;
            if (!in_array($method, $this->availableResizeMethods)) {
                throw new Exception('Unknown resize method: ' . $method);
            }
            $options = isset($size['options']) ? $size['options'] : $this->options;

            $watermark = isset($size['watermark']) ? $size['watermark'] : $this->watermark;
            if (($width || $height) && $method) {
                $newSource = $this->resize($source->copy(), $width, $height, $method);
                if ($watermark) {
                    $newSource = $this->applyWatermark($newSource, $watermark);
                }
                $this->getStorage()->save($this->sizeStoragePath($prefix, $this->value), $newSource->get($ext, $options), $force);
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
    public function sizeStoragePath($prefix, $value)
    {
        $dir = mb_substr_count($value, '/', 'UTF-8') > 0 ? dirname($value) : '';
        // TODO not working with cyrillic
        $filename = ltrim(mb_substr($value, mb_strlen($dir, 'UTF-8'), null, 'UTF-8'), '/');
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
        // TODO refactoring
        if ($this->getStorage() instanceof MimiBoxStorage) {
            $size = explode('x', $prefix);
            if (count($size) > 1) {
                list($width, $height) = $size;
            } else {
                $width = array_pop($size);
                $height = 0;
            }
            $path = $this->sizeStoragePath(null, $this->value);
            $path .= "?width=" . $width . '&height=' . $height;
            if ($this->force) {
                $path .= '&force=true';
            }
        } else {
            // Original file does not exists, return empty string
            if (!$this->getValue()) {
                return '';
            }
            $path = $this->sizeStoragePath($prefix, $this->value);
            if ($this->force || $this->checkMissing && !is_file($this->getStorage()->path($path))) {
                $absPath = $this->getStorage()->path($this->getValue());
                if ($absPath && is_file($absPath)) {
                    if ($this->_originalName != $absPath) {
                        $this->_originalName = $absPath;
                        $this->_original = $this->getImagine()->open($absPath);
                    }
                    $this->processSource($this->_original->copy(), true, [$prefix]);
                }
            }
        }
        return $this->getStorage()->url($path);
    }

    public function onAfterDelete()
    {
        $this->deleteOld();
    }

    public function toArray()
    {
        $sizes = [];
        if ($this->getValue()) {
            foreach ($this->sizes as $name => $params) {
                $sizes[$name] = $this->sizeUrl($name);
            }
            $sizes['original'] = $this->getStorage()->url($this->getValue());
        }
        return $sizes;
    }

    protected function findSizePrefix($prefix, $throw = true)
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

    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\ImageField', array $extra = [])
    {
        return parent::getFormField($form, $fieldClass, $extra);
    }
}
