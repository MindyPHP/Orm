<?php

namespace Mindy\Orm\Fields;

use Mindy\Base\Mindy;
use Mindy\Helper\Alias;
use Mindy\Orm\Traits\ImageProcess;
use Mindy\Storage\Files\File;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Point;


class ImageField extends FileField
{
    use ImageProcess;

    /**
     * Array with image sizes
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
     * Imagine default options
     * @var array
     */
    public $options = [
        'resolution-units' => ImageInterface::RESOLUTION_PIXELSPERINCH,
        'resolution-x' => 72,
        'resolution-y' => 72,
        'jpeg_quality' => 75,
        'png_compression_level' => 9
    ];

    /**
     * @var array|null
     *
     * example
     * [
     *  'file' => 'static.images.watermark.png',
     *  'position' => [200,100]
     * ]
     *
     * OR
     *
     * [
     *  'file' => 'static.images.watermark.png',
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
    public $defaultResize = 'adaptiveResize';

    public function setFile(File $file, $name = null)
    {
        $name = $name ? $name : $file->name;

        if ($name){
            $imagineSource = $this->getImagine()->open($file->path);
            $this->value = $this->makeFilePath($name);

            $imagineSource = $this->processSource($imagineSource);

            $this->getStorage()->save($this->value, $imagineSource->__toString());
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

    public function processSource($imagineSource)
    {
        $ext = pathinfo($this->getCleanValue(), PATHINFO_EXTENSION);
        foreach ($this->sizes as $prefix => $size) {
            $width = isset($size[0]) ? $size[0] : null;
            $height = isset($size[1]) ? $size[1] : null;
            $method = isset($size['method']) ? $size['method'] : $this->defaultResize;
            $options = isset($size['options']) ? $size['options'] : $this->options;
            $watermark = isset($size['watermark']) ? $size['watermark'] : $this->watermark;
            if ($width && $height && $method) {
                $newSource = $this->resize($imagineSource, $width, $height, $method);
                $newSource = $this->applyWatermark($newSource, $watermark);
                $this->getStorage()->save($this->sizeStoragePath($prefix), $newSource->get($ext, $options));
            }
        }

        return $this->applyWatermark($imagineSource, $this->watermark);
    }

    /**
     * @param $prefix
     * @param null $value
     * @return string
     */
    public function sizeStoragePath($prefix, $value = null)
    {
        $value = $value ? $value : $this->getCleanValue();
        $dir = dirname($value);
        $filename = basename($value);
        return ($dir ? $dir . DIRECTORY_SEPARATOR : '') . $prefix . $filename;
    }

    /**
     * @param $prefix
     * @return mixed
     */
    public function sizeUrl($prefix)
    {
        $path = $this->sizeStoragePath($prefix);
        return $this->getStorage()->url($path);
    }
}