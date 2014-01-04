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
 * @date 03/01/14.01.2014 22:03
 */

namespace Mindy\Db\Fields;


use Exception;
use Mindy\Db\Relation;

class ForeignField extends RelatedField
{
    public $onDelete;

    public $onUpdate;

    public function __construct($modelClass, array $options = [])
    {
        parent::__construct($options);

        $link = [];
        $relation = new Relation([
            'modelClass' => $modelClass,
            'primaryModel' => $this,
            'link' => $link,
            'multiple' => false,
        ]);
    }

    public function setValue($value)
    {
        if(is_a($value, '\Mindy\Db\Model') === false) {
            throw new Exception("value must be a Mindy\\Db\\Model instance. " . gettype($value) . "given");
        }

        $this->value = $value;
    }

    public function getOnDelete()
    {
        return $this->onDelete;
    }

    public function getOnUpdate()
    {
        return $this->onUpdate;
    }
}
