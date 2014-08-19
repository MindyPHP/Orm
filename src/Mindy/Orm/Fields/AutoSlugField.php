<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 27/05/14.05.2014 16:43
 */

namespace Mindy\Orm\Fields;


use Mindy\Helper\Meta;
use Mindy\Query\Expression;

class AutoSlugField extends CharField
{
    /**
     * @var string
     */
    public $source;
    /**
     * @var string|null
     */
    protected $oldValue;

    public function onBeforeInsert()
    {
        $this->value = empty($this->value) ? $this->getModel()->{$this->source} : ltrim($this->value, '/');
        $this->getModel()->setAttribute($this->name, $this->value);
    }

    public function onBeforeUpdate()
    {
        /** @var $model \Mindy\Orm\TreeModel */
        $model = $this->getModel();

        // if remove parent (parent is null)
        if (!$model->parent) {
            if(strpos($model->{$this->name}, '/') === false) {
                $url = $model->{$this->name};
            } else {
                $url = Meta::cleanString($model->{$this->source});
            }

            $model->setAttribute($this->name, $url);
        } else {
            $url = $model->{$this->name};

            $url = $model->parent->{$this->name} . '/' . $url;
            $model->setAttribute($this->name, $url);
        }

        $model->tree()->descendants()->update([
            $this->name => new Expression("REPLACE(`{$this->name}`, '{$model->getOldAttribute($this->name)}', '{$url}')")
        ]);
    }

    public function getDbPrepValue()
    {
        /*
         * Если передан уже конечный сформированный урл,
         * то не пытаемся обработать его дальше
         */
        if(strpos($this->getValue(), '/') !== false) {
            return $this->getValue();
        } else {
            $slugs = [
                Meta::cleanString($this->getValue())
            ];
            if ($parent = $this->getModel()->parent) {
                $slugs[] = $parent->{$this->name};
            }
            return implode('/', array_reverse($slugs));
        }
    }

    public function getFormValue()
    {
        $slugs = explode('/', $this->getValue());
        return end($slugs);
    }

    public function getFormField($form, $fieldClass = null)
    {
        return parent::getFormField($form, \Mindy\Form\Fields\ShortUrlField::className());
    }
}
