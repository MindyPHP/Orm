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
     * @var string
     */
    protected $basePath;
    /**
     * @var array
     */
    protected $availableResizeMethods = [
        'resize',
        'adaptiveResize',
        'adaptiveResizeFromTop'
    ];

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

        if (is_null($this->basePath)) {
            throw new Exception('basePath is empty');
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
     * @param SplFileInfo|string $fileInfo
     * @param string $prefix
     * @return string
     * @throws Exception
     */
    public function path($fileInfo, string $prefix) : string
    {
        if (is_string($fileInfo)) {
            $fileInfo = new \SplFileInfo($this->getBasePath() . DIRECTORY_SEPARATOR . ltrim($fileInfo, DIRECTORY_SEPARATOR));
        }

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

        $path = $this->generatePath($fileInfo, $options);

        if ($options['force'] ?? false || ($options['checkMissing'] ?? false && !$this->has($path))) {
            $this->process($fileInfo);
        }

        return $path;
    }

    /**
     * @param SplFileInfo|string $fileInfo
     * @param string $prefix
     * @return string
     * @throws Exception
     */
    public function url($fileInfo, string $prefix) : string
    {
        return substr($this->path($fileInfo, $prefix), strlen($this->getBasePath()));
    }

    /**
     * @param SplFileInfo $file
     * @param ImageInterface $image
     * @param null $prefix
     * @throws Exception
     */
    public function processSource(SplFileInfo $file, ImageInterface $image, $prefix = null)
    {
        foreach ($this->getSizes() as $name => $config) {

            /**
             * Skip unused sizes
             */
            if (!is_null($prefix) && $name !== $prefix) {
                continue;
            }

            if ($config instanceof \Closure) {
                $image = $config->__invoke($file, $image);
            } else {
                $width = $config['width'] ?? null;
                $height = $config['height'] ?? null;
                $method = $config['method'] ?? $this->defaultResize;
                $options = $config['config'] ?? [];
                $extSize = $config['format'] ?? $file->getExtension();

                if (!in_array($method, $this->availableResizeMethods)) {
                    throw new Exception('Unknown resize method: ' . $method);
                }

                if (!$width || !$height) {
                    list($width, $height) = $this->imageScale($image, $width, $height);
                }

                $newSource = $this->resize($image->copy(), $width, $height, $method);
                if (isset($config['watermark'])) {
                    $newSource = $this->applyWatermark($newSource, $config['watermark']);
                }

                $sizePath = $this->generatePath($file, $config);
                $this->write($sizePath, $newSource->get($extSize, $options));
            }
        }

        if ($this->storeOriginal) {
            $sizePath = $this->generatePath($file, ['original' => true]);
            if ($this->write($sizePath, $this->read($file->getRealPath())) === false) {
                throw new Exception("Failed to save original file");
            }
        }
    }

    /**
     * @param SplFileInfo $file
     * @param array $options
     * @return string
     */
    public function generateFilename(SplFileInfo $file, array $options = []) : string
    {
        ksort($options);
        $hash = substr(md5(serialize($options)), 0, 10);
        $name = $file->getFilename();
        $basename = substr($name, 0, strpos($name, $file->getExtension()) - 1);
        return implode('_', [$basename, $hash]) . '.' . $file->getExtension();
    }

    /**
     * @param SplFileInfo $file
     * @param array $options
     * @return string
     * @throws Exception
     */
    public function generatePath(SplFileInfo $file, array $options = []) : string
    {
        $filename = $this->generateFilename($file, $options);
        if (isset($options['uploadTo'])) {
            if (!is_dir($options['uploadTo'])) {
                throw new Exception('Directory is not available or not exists');
            }
            return $options['uploadTo'] . DIRECTORY_SEPARATOR . $filename;
        } else {
            return $this->getBasePath() . DIRECTORY_SEPARATOR . $filename;
        }
    }

    /**
     * @return string
     */
    protected function getBasePath() : string
    {
        return $this->basePath;
    }

    /**
     * @param string|\SplFileInfo $fileInfo
     * @param null $prefix
     * @return $this
     * @throws Exception
     */
    public function process($fileInfo, $prefix = null)
    {
        if (is_string($fileInfo)) {
            $fileInfo = new SplFileInfo($fileInfo);
        }

        // Create new sized files
        $image = $this->getImagine()->open($fileInfo->getRealPath());

        $this->processSource($fileInfo, $image, $prefix);

        return $this;
    }
}