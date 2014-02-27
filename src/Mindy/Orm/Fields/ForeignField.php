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

namespace Mindy\Orm\Fields;


use Exception;
use InvalidArgumentException;
use Mindy\Orm\Relation;

class ForeignField extends RelatedField
{
    public $onDelete;

    public $onUpdate;

    public $modelClass;

    public function __construct($modelClass, array $options = [])
    {
        parent::__construct($options);

        if(is_subclass_of($modelClass, '\Mindy\Orm\Model') === false) {
            throw new InvalidArgumentException('$modelClass must be a \Mindy\Orm\Model instance');
        } else {
            $this->modelClass = $modelClass;
        }

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
        if(is_a($value, $this->modelClass) === false) {
            $modelClass = $this->modelClass;
            $value = $modelClass::objects()->filter(['pk' => $value])->get();
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

    public function getForeignPrimaryKey()
    {
        $modelClass = $this->modelClass;
        return $modelClass::primaryKey();
    }
}
