<?php

namespace Mindy\Orm\Fields;

use Mindy\Orm\Validation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ImageField
 * @package Mindy\Orm
 */
class ImageField extends FileField
{
    /**
     * @var array
     */
    public $mimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
    ];

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
        $constraints = parent::getValidationConstraints();

        if (empty($this->value)) {
            return array_merge($constraints, [
                new Validation\Image([
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

        return $constraints;
    }

    /**
     * @param string $fieldClass
     * @return false|null|string
     */
    public function getFormField($fieldClass = '\Mindy\Form\Fields\ImageField')
    {
        return parent::getFormField($fieldClass);
    }
}
