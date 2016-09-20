<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 20/09/16
 * Time: 14:03
 */

namespace Mindy\Orm\Image;

use Exception;
use Imagine\Image\ImageInterface;
use SplFileInfo;

/**
 * Class ImageProcessor
 * @package Mindy\Orm\Image
 *
 * watermark:
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
class ImageProcessor extends AbstractProcessor implements ImageProcessorInterface
{
    use ImageProcess;

    /**
     * @var bool
     */
    public $storeOriginal = true;
    /**
     * Default resize method
     * @var string
     */
    public $defaultResize = 'adaptiveResizeFromTop';
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
     * @var array
     */
    protected $availableResizeMethods = [
        'resize',
        'adaptiveResize',
        'adaptiveResizeFromTop'
    ];
    /**
     * @var string
     */
    protected $uploadTo;

    /**
     * ImageProcessor constructor.
     * @param array $config
     * @throws Exception
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @return array
     */
    public function getSizes() : array
    {
        return $this->sizes;
    }

    /**
     * @param string $prefix
     * @return array
     * @throws Exception
     */
    protected function findOptionsByPrefix(string $prefix) : array
    {
        $sizes = $this->getSizes();

        if (strpos($prefix, 'x') !== false) {
            list($width, $height) = explode('x', $prefix);
            $options = null;
            foreach ($sizes as $prefix => $config) {
                if ($config['width'] ?? null === $width && $config['height'] ?? null === $height) {
                    $options = $config;
                    break;
                }
            }

            if ($options === null) {
                throw new Exception('Unknown sizes. Failed to find prefix for width: ' . $width . ' and height: ' . $height);
            }
        } else if (isset($sizes[$prefix])) {
            $options = $sizes[$prefix];
        } else {
            throw new Exception('Unknown prefix');
        }

        return $options;
    }

    /**
     * @param string $value
     * @param string $prefix
     * @return string
     * @throws Exception
     */
    public function path(string $value, string $prefix) : string
    {
        $options = $this->findOptionsByPrefix($prefix);

        if ($options['force'] ?? false || ($options['checkMissing'] ?? false && !$this->has($value))) {
            $this->process($value);
        }

        return $value;
    }

    /**
     * @param string $value
     * @param string $prefix
     * @param array $config
     * @return string
     */
    public function url(string $value, string $prefix, array $config = []) : string
    {
        $options = $this->findOptionsByPrefix($prefix);

        if ($options['force'] ?? false || ($options['checkMissing'] ?? false && !$this->has($value))) {
            $this->process($value);
        }

        $fileName = $this->generateFilename($value, $config);
        return $this->getFilesystem()->url($fileName, $config);
    }

    /**
     * @param string $path
     * @param ImageInterface $image
     * @param null $prefix
     * @return $this
     * @throws Exception
     */
    public function processSource(string $path, ImageInterface $image, $prefix = null)
    {
        foreach ($this->getSizes() as $name => $config) {

            /**
             * Skip unused sizes
             */
            if (!is_null($prefix) && $name !== $prefix) {
                continue;
            }

            if ($config instanceof \Closure) {
                $image = $config->__invoke($path, $image);
            } else {
                $width = $config['width'] ?? null;
                $height = $config['height'] ?? null;
                $method = $config['method'] ?? $this->defaultResize;
                $options = $config['config'] ?? [];
                $extSize = $config['format'] ?? pathinfo($path, PATHINFO_EXTENSION);

                if (!in_array($method, $this->availableResizeMethods)) {
                    throw new Exception('Unknown resize method: ' . $method);
                }

                if (!$width || !$height) {
                    list($width, $height) = $this->imageScale($image, $width, $height);
                }

                $newSource = $this->resize($image->copy(), $width, $height, $method);
                if (isset($config['watermark'])) {
                    $watermarkConfig = $config['watermark'];
                    if (!isset($watermarkConfig['file']) || !$this->has($watermarkConfig['file'])) {
                        throw new Exception('Watermark image missing or not exists');
                    }

                    $watermark = self::getImagine()->load($this->getFilesystem()->read($watermarkConfig['file']));
                    $newSource = $this->applyWatermark($newSource, $watermark, $watermarkConfig['position'] ?? 'center');
                }

                $sizePath = $this->generateFilename($path, $config);
                $resultPath = $this->uploadTo . DIRECTORY_SEPARATOR . $sizePath;
                if ($this->has($resultPath)) {
                    $this->getFilesystem()->delete($resultPath);
                }
                $this->write($resultPath, $newSource->get($extSize, $options));
            }
        }

        if ($this->storeOriginal) {
            $sizePath = $this->generateFilename($path, ['original' => true]);
            $resultPath = $this->uploadTo . DIRECTORY_SEPARATOR . $sizePath;
            if ($this->has($resultPath)) {
                $this->getFilesystem()->delete($resultPath);
            }
            if ($this->write($resultPath, $this->read($path)) === false) {
                throw new Exception("Failed to save original file");
            }
        }

        return $this;
    }

    /**
     * @param string $path
     * @param array $options
     * @return string
     */
    public function generateFilename(string $path, array $options = []) : string
    {
        ksort($options);
        $hash = substr(md5(serialize($options)), 0, 10);
        $dir = dirname($path);
        $name = basename($path);
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $basename = substr($name, 0, strpos($name, $ext) - 1);
        return $dir . DIRECTORY_SEPARATOR . implode('_', [$basename, $hash]) . '.' . $ext;
    }

    /**
     * @param string $path
     * @param null $prefix
     * @return $this
     * @throws Exception
     */
    public function process(string $path, $prefix = null)
    {
        if (!is_file($path)) {
            throw new Exception('File not found');
        }

        $image = $this->getImagine()->open($path);
        return $this->processSource($path, $image, $prefix);
    }
}