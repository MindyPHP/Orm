<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 30/09/16
 * Time: 17:49
 */

namespace Mindy\Bundle\MindyBundle\Library;

use Exception;
use Imagine\Image\ImageInterface;
use Mindy\Orm\Image\ImageProcess;
use Mindy\Orm\Traits\FilesystemAwareTrait;
use Mindy\Template\Library;

class ImageLibrary extends Library
{
    use ImageProcess;
    use FilesystemAwareTrait;

    /**
     * @return array
     */
    public function getHelpers()
    {
        return [
            'thumb' => function ($path, $width, $height = null, $method = 'adaptiveResize', $watermark = [], $options = []) {
                if (empty($path)) {
                    return sprintf('http://placehold.it/%sx%s', $width, $height);
                }

                $generateConfig = [$path, $width, $height, $method, $watermark, $options];
                $fs = $this->getFilesystem();
                $newPath = $this->generateFilename($path, $generateConfig);
                if ($fs->has($newPath) == false) {
                    $newPath = $this->process($path, $width, $height, $method, $watermark, $options);
                }

                return $newPath;
            }
        ];
    }

    protected function process($path, $width, $height = null, $method = 'adaptiveResize', $watermark = [], $options = [])
    {
        static $resizeMethods = ['adaptiveResize', 'resize', 'adaptiveResizeFromTop'];

        $generateConfig = [$path, $width, $height, $method, $watermark, $options];

        $options = array_merge([
            'resolution-units' => ImageInterface::RESOLUTION_PIXELSPERINCH,
            'resolution-x' => 72,
            'resolution-y' => 72,
            'jpeg_quality' => 100,
            'quality' => 100,
            'png_compression_level' => 0
        ], $options);

        if (!in_array($method, $resizeMethods)) {
            throw new Exception('Unknown resize method: ' . $method);
        }

        $fs = $this->getFilesystem();
        $file = $fs->get($path);

        $imagine = self::getImagine();
        $image = $imagine->load($file->read());

        if (!$width || !$height) {
            list($width, $height) = $this->imageScale($image, $width, $height);
        }

        $newSource = $this->resize($image->copy(), $width, $height, $method);
        if (!empty($watermark)) {
            if (is_array($watermark)) {
                list($watermarkFile, $watermarkPosition) = $watermark;
            } else {
                $watermarkFile = $watermark;
                $watermarkPosition = 'center';
            }
            $watermark = $imagine->open($watermarkFile);
            $newSource = $this->applyWatermark($newSource, $watermark, $watermarkPosition);
        }

        $sizePath = $this->generateFilename($path, $generateConfig);
        $fs->write($sizePath, $newSource->get(pathinfo($path, PATHINFO_EXTENSION), $options));
        return $sizePath;
    }

    /**
     * @param string $path
     * @param array $options
     * @return string
     */
    public function generateFilename(string $path, array $options = []) : string
    {
        $hash = md5(json_encode($options));
        $newFilename = implode('_', [pathinfo($path, PATHINFO_FILENAME), $hash]) . '.' . pathinfo($path, PATHINFO_EXTENSION);
        return pathinfo($path, PATHINFO_DIRNAME) . '/' . $newFilename;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return [];
    }
}