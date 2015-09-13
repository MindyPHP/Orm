<?php

namespace Mindy\Orm\Fields;

use Exception;
use InvalidArgumentException;
use Mindy\Orm\Orm;
use Mindy\Orm\RelatedManager;

/**
 * Class ForeignField
 * @package Mindy\Orm
 */
class ForeignField extends RelatedField
{
    public $onDelete;

    public $onUpdate;

    public $modelClass;

    public $extra = [];

    public function init()
    {
        if (is_subclass_of($this->modelClass, '\Mindy\Orm\Model') === false) {
            throw new InvalidArgumentException('$modelClass must be a \Mindy\Orm\Model instance');
        }
    }

    public function setValue($value)
    {
        if (is_a($value, $this->modelClass) === false) {
            $tmp = $this->fetch($value);
            if ($tmp) {
                $value = $tmp;
            }
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
        if (is_a($this->value, Orm::className())) {
            return $this->value->pk;
        }
        return $this->value;
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

        return [
            $this->getRelatedModel(),
            [
                [
                    'table' => $this->getRelatedTable(false),
                    // @TODO: chained with Sync - 40 line
                    'from' => $column . '_id',
                    'to' => $this->getRelatedModel()->getPkName(),
                ]
            ]
        ];
    }

    /**
     * @return \Mindy\Orm\ManyToManyManager QuerySet of related objects
     */
    public function getManager()
    {
        // TODO move query to fetch method
        $manager = new RelatedManager($this->getRelatedModel());
        $value = $this->getValue();
        return is_object($value) ? $value : $manager->filter(array_merge(['pk' => $value], $this->extra));
    }

    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\DropDownField', array $extra = [])
    {
        return parent::getFormField($form, $fieldClass, $extra);
    }

    /**
     * @param $value
     * @return \Mindy\Orm\Model|\Mindy\Orm\TreeModel|null
     * @throws Exception
     */
    protected function fetch($value)
    {
        if (empty($value)) {
            if ($this->null == true) {
                return null;
            } else {
                throw new Exception("Value in fetch method of PrimaryKeyField cannot be empty");
            }
        }

        /** @var $modelClass \Mindy\Orm\Model */
        $modelClass = $this->modelClass;
        return $modelClass::objects()->filter(array_merge(['pk' => $value], $this->extra))->get();
    }
}
