<?php

namespace Mindy\Orm\Fields;

use Exception;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Mindy\Base\Mindy;
use Mindy\Exception\WarningException;
use Mindy\Orm\Traits\ImageProcess;
use Mindy\Storage\Files\File;
use Mindy\Storage\FileSystemStorage;
use Mindy\Storage\Interfaces\IExternalStorage;
use Mindy\Storage\MimiBoxStorage;
use Mindy\Helper\File as FileHelper;

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

    /**
     * @param array $params
     * @return string
     */
    public function getUrl(array $params = [])
    {
        return $this->getFileSystem()->url($this->value, $params);
    }

    public function setFile(File $file, $name = null)
    {
        $name = $name ? $name : $file->name;

        if ($this->MD5Name) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $name = md5($name) . '.' . $ext;
        }

        if ($name) {
            $this->value = $this->makeFilePath($name);
            $fileContent = $file->getContent();

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
                    if ($this->getFileSystem()->write($this->value, $fileContent) === false) {
                        throw new Exception("Failed to save original file");
                    }
                }
            } else {
                $this->value = null;
            }
        }

        return $this->value;
    }

    public function deleteOld()
    {
        if ($this->getOldValue()) {
            $fs = $this->getFileSystem();
            if ($fs->has($this->getOldValue())) {
                $fs->delete($this->getOldValue());
            }
            foreach (array_keys($this->sizes) as $prefix) {
                $path = $this->sizeStoragePath($prefix, $this->getOldValue());
                if ($fs->has($path)) {
                    $fs->delete($path);
                }
            }
        }
    }

    /**
     * @param $source
     * @param bool $force
     * @param array|null $onlySizes - Resize and save only sizes, described in this array
     * @return
     * @throws Exception
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
            $extSize = isset($size['format']) ? $size['format'] : $ext;

            $watermark = isset($size['watermark']) ? $size['watermark'] : $this->watermark;
            if (($width || $height) && $method) {
                $newSource = $this->resize($source->copy(), $width, $height, $method);
                if ($watermark) {
                    $newSource = $this->applyWatermark($newSource, $watermark);
                }
                $fs = $this->getFileSystem();
                $sizePath = $this->sizeStoragePath($prefix, $this->value);
                if ($force && $fs->has($sizePath)) {
                    $fs->delete($sizePath);
                }
                $this->getFileSystem()->write($sizePath, $newSource->get($extSize, $options));
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

        $sizeOptions = isset($this->sizes[$prefix]) ? $this->sizes[$prefix] : [];
        $prefix = $prefix === null ? '' : $this->preparePrefix($prefix);

        if (isset($sizeOptions['format'])) {
            $name = FileHelper::mbPathinfo($filename, PATHINFO_FILENAME);
            $filename = $name . '.' . $sizeOptions['format'];
        }
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
        // Original file does not exists, return empty string
        if (!$this->getValue()) {
            return '';
        }
        $fs = $this->getFileSystem();

        if ($fs instanceof \Mimibox\Flysystem\Mimibox) {
            if (strpos($prefix, 'x') === false) {
                $params = $this->sizes[$prefix];
                list($width, $height) = $params;
            } else {
                $sizes = explode('x', $prefix);
                $width = $sizes[0];
                $height = $sizes[1];
            }

            return $this->getUrl([
                'width' => $width,
                'height' => $height
            ]);
        } else {
            $path = $this->sizeStoragePath($prefix, $this->value);
            if ($this->force || $this->checkMissing && !$fs->has($path)) {
                if ($fs->has($this->getValue())) {
                    if ($this->_originalName != $this->getValue()) {
                        $this->_originalName = $this->getValue();
                        $this->_original = $this->getImagine()->load($fs->read($this->getValue()));
                    }
                    $this->processSource($this->_original->copy(), true, [$prefix]);
                }
            }

            return $this->getStorage()->url($path);
        }
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
