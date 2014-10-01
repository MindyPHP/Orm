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
    public $source = 'name';
    /**
     * @var string|null
     */
    protected $oldValue;

    public function uniqueUrl($url, $count = 0, $pk = null)
    {
        $model = $this->getModel();
        $newUrl = $url;
        if ($count) {
            $newUrl .= '-' . $count;
        }
        $qs = $model::objects()->filter([$this->getName() => $newUrl]);
        if ($pk) {
            $qs = $qs->exclude(['pk' => $pk]);
        }
        if ($qs->count() > 0) {
            $count++;
            return $this->uniqueUrl($url, $count, $pk);
        }
        return $newUrl;
    }

    public function onBeforeInsert()
    {
        $model = $this->getModel();
        $this->value = empty($this->value) ? Meta::cleanString($model->{$this->source}) : ltrim($this->value, '/');
        if($model->parent) {
            $url = $model->parent->{$this->name} . '/' . $this->value;
        } else {
            $url = $this->value;
        }

        $url = '/' . ltrim($url, '/');
        $url = $this->uniqueUrl($url);

        $model->setAttribute($this->name, $url);
    }

    public function onBeforeUpdate()
    {
        /** @var $model \Mindy\Orm\TreeModel */
        $model = $this->getModel();

        // Случай когда обнулен slug, например из админки
        if(empty($model->{$this->name})) {
            $model->{$this->name} = Meta::cleanString($model->{$this->source});
        }

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
        } else {
            $parentUrl = $model->parent->{$this->name};
            $slugs = explode('/', $model->{$this->name});
            $url = $parentUrl . '/' . end($slugs);
        }

        $url = '/' . ltrim($url, '/');
        $url = $this->uniqueUrl($url, 0, $model->pk);

        $model->setAttribute($this->name, $url);

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
