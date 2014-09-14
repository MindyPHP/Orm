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

class SlugField extends CharField
{
    /**
     * @var string
     */
    public $source = 'name';

    public function onBeforeInsert()
    {
        $model = $this->getModel();
        $this->value = empty($this->value) ? Meta::cleanString($model->{$this->source}) : $this->value;
        $model->setAttribute($this->name, $this->value);
    }

    public function onBeforeUpdate()
    {
        /** @var $model \Mindy\Orm\TreeModel */
        $model = $this->getModel();

        // Случай когда обнулен slug, например из админки
        if (empty($model->{$this->name})) {
            $model->{$this->name} = Meta::cleanString($model->{$this->source});
        }
        $model->setAttribute($this->name, Meta::cleanString($model->{$this->name}));
    }
}
