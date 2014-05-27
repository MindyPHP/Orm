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
    public $source;

    public function onBeforeInsert()
    {
        $this->value = empty($this->value) ? $this->getModel()->{$this->source} : ltrim($this->value,  '/');
        $this->getModel()->setAttribute($this->name, $this->value);
    }

    public function onBeforeUpdate()
    {
        $this->value = empty($this->value) ? $this->getModel()->{$this->source} : ltrim($this->value, '/');
        $this->getModel()->setAttribute($this->name, $this->value);
    }

    public function getDbPrepValue()
    {
        return $this->getRecursiveValue();
    }

    public function getRecursiveValue()
    {
        $parent = $this->getModel();
        $slugs = [
            Meta::cleanString($this->getValue())
        ];
        while(($parent = $parent->parent) !== null) {
            $slugs[] = $parent->{$this->source};
        }

        return implode('/', array_reverse($slugs));
    }

    public function onAfterUpdate()
    {
        $model = $this->getModel();
        $oldUrl = $model->getOldAttribute($this->name);
        $url = $model->{$this->name};
        $alias = $model->tree()->getQuerySet()->getTableAlias();

        $model->tree()->descendants()->update([
            $this->name => new Expression("REPLACE({$alias}.`{$this->name}`, '{$oldUrl}', '{$url}')")
        ]);
    }
}
