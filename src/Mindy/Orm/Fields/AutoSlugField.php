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
        $model = $this->getModel();
        $this->value = empty($this->value) ? $model->{$this->source} : ltrim($this->value, '/');
        if($model->parent) {
            $url = $model->parent->{$this->name} . '/' . $this->value;
        } else {
            $url = $this->value;
        }

        $url = '/' . ltrim($url, '/');
        $model->setAttribute($this->name, $url);
    }

    public function onBeforeUpdate()
    {
        /** @var $model \Mindy\Orm\TreeModel */
        $model = $this->getModel();

        // if remove parent (parent is null)
        if (!$model->parent) {
            if(strpos($model->{$this->name}, '/') === false) {
                $url = Meta::cleanString($model->{$this->name});
            } else {
                if($model->{$this->name}) {
                    $slugs = explode('/', $model->{$this->name});
                    $url = end($slugs);
                } else {
                    $url = Meta::cleanString($model->{$this->source});
                }
            }

            $url = '/' . ltrim($url, '/');
            $model->setAttribute($this->name, $url);
        } else {
            $parentUrl = $model->parent->{$this->name};
            $slugs = explode('/', $model->{$this->name});
            $url = $parentUrl . '/' . end($slugs);
            $url = '/' . ltrim($url, '/');
            $model->setAttribute($this->name, $url);
        }

        $model->tree()->filter([
            'lft__gt' => $model->getOldAttribute('lft'),
            'rgt__lt' => $model->getOldAttribute('rgt'),
            'root' => $model->getOldAttribute('root')
        ])->update([
            $this->name => new Expression("REPLACE(`{$this->name}`, '{$model->getOldAttribute($this->name)}', '{$url}')")
        ]);
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
