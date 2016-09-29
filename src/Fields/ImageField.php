<?php

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Mindy\Orm\Files\File;
use Mindy\Orm\Image\ImageProcessor;
use Mindy\Orm\Image\ImageProcessorInterface;
use Mindy\Orm\ModelInterface;
use GuzzleHttp\Psr7\UploadedFile as GuzzleUploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ImageField
 * @package Mindy\Orm
 */
class ImageField extends FileField
{
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
    public $mimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
    ];
    /**
     * @var ImageProcessor
     */
    protected $processor;

    /**
     * @var Assert\Image validation settings
     */
    public $minWidth;
    public $maxWidth;
    public $maxHeight;
    public $minHeight;
    public $maxRatio;
    public $minRatio;
    public $allowSquare = true;
    public $allowLandscape = true;
    public $allowPortrait = true;
    public $detectCorrupted = false;

    /**
     * @return array
     */
    public function getValidationConstraints() : array
    {
        return array_merge(parent::getValidationConstraints(), [
            new Assert\Image([
                'minWidth' => $this->minWidth,
                'maxWidth' => $this->maxWidth,
                'maxHeight' => $this->maxHeight,
                'minHeight' => $this->minHeight,
                'maxRatio' => $this->maxRatio,
                'minRatio' => $this->minRatio,
                'allowSquare' => $this->allowSquare,
                'allowLandscape' => $this->allowLandscape,
                'allowPortrait' => $this->allowPortrait,
                'detectCorrupted' => $this->detectCorrupted,
            ])
        ]);
    }

    /**
     * @param ImageProcessorInterface $processor
     * @return $this
     */
    public function setProcessor(ImageProcessorInterface $processor)
    {
        $this->processor = $processor;
        return $this;
    }

    /**
     * @param \Mindy\Orm\Model|ModelInterface $model
     * @param $value
     */
    public function afterDelete(ModelInterface $model, $value)
    {
        parent::afterDelete($model, $value);

        $processor = $this->getProcessor();
        foreach ($processor->getSizes() as $config) {
            $path = $processor->path($value, $config);
            $fs = $this->getFilesystem();
            if ($fs->has($path)) {
                $fs->delete($path);
            }
        }
    }

    /**
     * @param \Mindy\Orm\Model|ModelInterface $model
     * @param $value
     */
    public function afterUpdate(ModelInterface $model, $value)
    {
        parent::afterUpdate($model, $value);

        if ($model->hasAttribute($this->getAttributeName())) {
            if (
                ($oldValue = $model->getOldAttribute($this->getAttributeName())) &&
                $value != $oldValue
            ) {
                $this->getProcessor()->process($value, $this->getUploadTo());
            }
        }
    }

    /**
     * @param array $options
     * @return string
     */
    public function path(array $options = []) : string
    {
        return $this->getProcessor()->path($this->value, $options);
    }

    /**
     * @param array $options
     * @return string
     */
    public function url(array $options = []) : string
    {
        return $this->getProcessor()->url($this->value, $options);
    }

    /**
     * @return ImageProcessor
     */
    protected function getProcessor()
    {
        if ($this->processor === null) {
            $this->processor = new ImageProcessor([
                'sizes' => $this->sizes,
                'uploadTo' => $this->getUploadTo()
            ]);
        }
        return $this->processor;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $sizes = [];
        if ($this->getValue()) {
            $processor = $this->getProcessor();
            foreach ($processor->getSizes() as $config) {
                $sizes[$config['name']] = $this->url($config);
            }
            $sizes['original'] = $this->url($this->value);
        }
        return $sizes;
    }

    /**
     * @param string $fieldClass
     * @return false|null|string
     */
    public function getFormField($fieldClass = '\Mindy\Form\Fields\ImageField')
    {
        return parent::getFormField($fieldClass);
    }

    public function beforeInsert(ModelInterface $model, $value)
    {
        if ($value) {
            $this->resizeAndSaveImage($model, $value);
        }
    }

    public function beforeUpdate(ModelInterface $model, $value)
    {
        if (in_array($this->getName(), $model->getDirtyAttributes()) && $value) {
            $this->resizeAndSaveImage($model, $value);
        }
    }

    private function resizeAndSaveImage(ModelInterface $model, $value)
    {
        if ($value instanceof GuzzleUploadedFile) {
            $value = $this->saveGuzzleFile($value);
        } else if ($value instanceof File) {
            $value = $this->saveFile($value);
        }
        $value = $this->normalizeValue($value);

        $realPath = $this->getFilesystem()->getAdapter()->applyPathPrefix($value);
        if (is_file($realPath)) {
            $this->getProcessor()->process($realPath);
        }
    }
}
