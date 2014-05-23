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


use InvalidArgumentException;
use Mindy\Orm\Relation;

class ForeignField extends RelatedField
{
    public $onDelete;

    public $onUpdate;

    public $modelClass;

    public function init()
    {
        if (is_subclass_of($this->modelClass, '\Mindy\Orm\Model') === false) {
            throw new InvalidArgumentException('$modelClass must be a \Mindy\Orm\Model instance');
        }
    }

    public function setValue($value)
    {
        if (is_a($value, $this->modelClass) === false) {
            /** @var $modelClass \Mindy\Orm\Model */
            $modelClass = $this->modelClass;
            $value = $modelClass::objects()->filter(['pk' => $value])->get();
        }

        if(empty($value)) {
            $value = null;
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
        /** @var $modelClass \Mindy\Orm\Model */
        $pk = $modelClass::primaryKey();
        return array_shift($pk);
    }

    public function fetch($value)
    {
        $modelClass = $this->modelClass;
        /** @var $modelClass \Mindy\Orm\Model */
        return $modelClass::objects()->filter(['pk' => $value])->get();
    }

    public function getJoin()
    {
        return [$this->getRelatedModel(),  [
            [
                'table' => $this->getRelatedTable(false),
                // @TODO: chained with Sync - 40 line
                'from' => $this->getRelatedTable() . '_id',
                'to' => $this->getRelatedModel()->getPkName(),
            ]
        ]];
    }
}
