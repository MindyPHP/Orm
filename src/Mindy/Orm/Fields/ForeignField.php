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
use Mindy\Orm\Orm;
use Mindy\Orm\RelatedManager;
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

        if (empty($value)) {
            $value = null;
        }
        $this->value = $value;
        return $this;
    }

    public function getOnDelete()
    {
        return $this->onDelete;
    }

    public function getOnUpdate()
    {
        return $this->onUpdate;
    }

    public function getDbPrepValue()
    {
        if ($this->value && is_a($this->value, Orm::className())) {
            return $this->value->pk;
        } else {
            return $this->value;
        }
    }

    public function getForeignPrimaryKey()
    {
        $modelClass = $this->modelClass;
        /** @var $modelClass \Mindy\Orm\Model */
        return $modelClass::getPkName();
    }

    public function getJoin()
    {
        /** @var \Mindy\Orm\Model $cls */
        $cls = $this->modelClass;
        $tmp = explode('\\', $cls);
        $column = $cls::normalizeTableName(end($tmp));

        return [$this->getRelatedModel(), [[
            'table' => $this->getRelatedTable(false),
            // @TODO: chained with Sync - 40 line
            'from' => $column . '_id',
            'to' => $this->getRelatedModel()->getPkName(),
        ]]];
    }

    /**
     * @return \Mindy\Orm\ManyToManyManager QuerySet of related objects
     */
    public function getManager()
    {
        $manager = new RelatedManager($this->getRelatedModel());
        $value = $this->getValue();
        if (is_object($value)) {
            return $value;
        } else {
            return $manager->filter(['pk' => $value]);
        }
    }

    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\DropDownField', array $extra = [])
    {
        return parent::getFormField($form, $fieldClass, $extra);
    }

    public function fetch($value)
    {
        // TODO: Implement fetch() method.
    }
}
