<?php

namespace Mindy\Orm\Fields;

use Cocur\Slugify\Slugify;
use Mindy\Helper\Meta;
use Mindy\Orm\ModelInterface;
use Mindy\Orm\Traits\UniqueUrl;
use Mindy\Query\ConnectionManager;
use Mindy\Query\Expression;

/**
 * Class AutoSlugField
 * @package Mindy\Orm
 */
class AutoSlugField extends CharField
{
    use UniqueUrl;

    /**
     * @var string
     */
    public $source = 'name';
    /**
     * @var bool
     */
    public $autoFetch = true;
    /**
     * @var string|null
     */
    protected $oldValue;

    /**
     * Internal event
     * @param \Mindy\Orm\TreeModel|ModelInterface $model
     * @param $value
     */
    public function beforeInsert(ModelInterface $model, $value)
    {
        if (empty($value)) {
            $slug = $this->createSlug($model->getAttribute($this->source));
        } else {
            $slug = $this->getLastSegment($value);
        }

        if ($model->parent) {
            $slug = $model->parent->getAttribute($this->getAttributeName()) . '/' . ltrim($slug, '/');
        }

        $model->setAttribute($this->getAttributeName(), $this->uniqueUrl(ltrim($slug, '/')));
    }

    /**
     * @param $source
     * @return string
     */
    protected function createSlug($source) : string
    {
        static $instance;
        if ($instance === null) {
            $instance = new Slugify();
        }
        return $instance->slugify($source);
    }

    /**
     * @param $slug
     * @return string
     */
    protected function getLastSegment($slug) : string
    {
        if (strpos($slug, '/') === false) {
            return $slug;
        } else {
            return substr($slug, strrpos($slug, '/', -1) + 1);
        }
    }

    /**
     * @param $slug
     * @return string
     */
    protected function getParentSegment($slug) : string
    {
        if (strpos($slug, '/') === false) {
            return $slug;
        } else {
            return substr($slug, 0, strrpos($slug, '/', -1));
        }
    }

    /**
     * Internal event
     * @param \Mindy\Orm\TreeModel|ModelInterface $model
     * @param $value
     */
    public function beforeUpdate(ModelInterface $model, $value)
    {
        if (empty($value)) {
            $slug = $this->createSlug($model->getAttribute($this->source));
        } else {
            $slug = $this->getLastSegment($value);
        }

        if ($model->parent) {
            $slug = implode('/', [
                $this->getParentSegment($model->parent->getAttribute($this->getAttributeName())),
                $slug
            ]);
        }

        $slug = $this->uniqueUrl(ltrim($slug, '/'), 0, $model->pk);

        $conditions = [
            'lft__gte' => $model->getAttribute('lft'),
            'rgt__lte' => $model->getAttribute('rgt'),
            'root' => $model->getAttribute('root')
        ];

        $attributeValue = $model->getOldAttribute($this->getAttributeName());
        if (empty($attributeValue)) {
            $attributeValue = $model->getAttribute($this->getAttributeName());
        }
        $expr = "REPLACE([[" . $this->getAttributeName() . "]], @" . $attributeValue . "@, @" . $slug . "@)";

        $qs = $model->objects()->filter($conditions);
        $qs->update([
            $this->getAttributeName() => new \Mindy\QueryBuilder\Expression($expr)
        ]);

        $model->setAttribute($this->getAttributeName(), $slug);
    }

    /**
     * @return mixed
     */
    public function getFormValue()
    {
        $slugs = explode('/', $this->getValue());
        return end($slugs);
    }

    /**
     * @param $form
     * @param string $fieldClass
     * @param array $extra
     * @return mixed|null
     */
    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\SlugField', array $extra = [])
    {
        return parent::getFormField($form, $fieldClass, $extra);
    }
}
