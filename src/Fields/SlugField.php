<?php

namespace Mindy\Orm\Fields;

use Cocur\Slugify\Slugify;
use Mindy\Orm\ModelInterface;
use Mindy\Orm\Traits\UniqueUrl;

/**
 * Class SlugField
 * @package Mindy\Orm
 */
class SlugField extends CharField
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

    public function beforeInsert(ModelInterface $model, $value)
    {
        $this->value = empty($this->value) ? $this->slugify($model->{$this->source}) : $this->value;
        if ($this->unique) {
            $this->value = $this->uniqueUrl($this->value);
        }
        $model->setAttribute($this->getAttributeName(), $this->value);
    }

    public function canBeEmpty()
    {
        return true;
    }

    protected function slugify($source)
    {
        return (new Slugify())->slugify($source);
    }

    public function beforeUpdate(ModelInterface $model, $value)
    {
        $this->value = $value;

        // Случай когда обнулен slug, например из админки
        if (empty($value)) {
            $this->value = $this->slugify($model->{$this->source});
        }

        if ($this->unique) {
            $this->value = $this->uniqueUrl($this->value, 0, $model->pk);
        }
        $model->setAttribute($this->getAttributeName(), $this->value);
    }
}
