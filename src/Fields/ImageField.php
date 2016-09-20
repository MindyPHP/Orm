<?php

namespace Mindy\Orm\Fields;

use Imagine\Image\Point;
use Mindy\Orm\Image\ImageProcessor;
use Mindy\Orm\Image\ImageProcessorInterface;
use Mindy\Orm\ModelInterface;
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
        foreach ($processor->getSizes() as $prefix => $config) {
            $path = $processor->path($value, $prefix);
            if (is_file($path)) {
                unlink($path);
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
                $this->getProcessor()->process($value);
            }
        }
    }

    /**
     * @param $prefix
     * @return string
     */
    public function path($prefix) : string
    {
        return $this->getProcessor()->path($this->value, $prefix);
    }

    /**
     * @param $prefix
     * @return string
     */
    public function url($prefix) : string
    {
        return $this->getProcessor()->url($this->value, $prefix);
    }

    /**
     * @return ImageProcessor
     */
    protected function getProcessor()
    {
        if ($this->processor === null) {
            $this->processor = new ImageProcessor([
                'sizes' => $this->sizes,
                'basePath' => $this->getBasePath()
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
            foreach ($processor->getSizes() as $prefix => $config) {
                $sizes[$prefix] = $this->url($prefix);
            }
            $sizes['original'] = $this->url($this->value);
        }
        return $sizes;
    }

    /**
     * @param $form
     * @param string $fieldClass
     * @param array $extra
     * @return null|object
     */
    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\ImageField', array $extra = [])
    {
        return parent::getFormField($form, $fieldClass, $extra);
    }
}
