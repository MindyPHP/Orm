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

class ForeignField extends RelatedField
{
    public $onDelete;

    public $onUpdate;

    public function __construct(array $options = [])
    {
        parent::__construct($options);

        // $relation = $this->hasOne($options['model'], )
        // TODO

        if($this->getRelation()->multiple) {
            throw new Exception("Incorrect relation");
        }
    }

    public function setValue($value)
    {
        if(is_a($value, 'Orm') === false) {
            throw new Exception("value must be a Orm instance. " . gettype($value) . "given");
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
