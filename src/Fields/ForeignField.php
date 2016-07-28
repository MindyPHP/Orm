<?php

namespace Mindy\Orm\Fields;

use Exception;
use InvalidArgumentException;
use Mindy\Orm\Base;
use Mindy\Orm\Orm;
use Mindy\Orm\RelatedManager;
use Mindy\QueryBuilder\QueryBuilder;

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
        parent::init();
        if (is_subclass_of($this->modelClass, '\Mindy\Orm\Model') === false) {
            throw new InvalidArgumentException('$modelClass must be a \Mindy\Orm\Model instance in modelClass');
        }
    }

    public function setValue($value)
    {
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
        if (is_a($this->value, Orm::class)) {
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

    public function getJoin(QueryBuilder $qb, $topAlias)
    {
        $alias = $qb->makeAliasKey($this->getRelatedModel()->tableName());
        return [
            [
                'LEFT JOIN',
                $this->getRelatedTable(false),
                [$topAlias . '.' . $this->name . '_id' => $alias . '.' . $this->getRelatedModel()->getPkName()],
                $alias
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

    public function getValue()
    {
        $value = parent::getValue();
        return is_a($value, $this->modelClass) === false ? $this->fetch($value) : $value;
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
            if ($this->null === true) {
                return null;
            } else {
                throw new Exception("Value in fetch method of PrimaryKeyField cannot be empty");
            }
        }

        /** @var $modelClass \Mindy\Orm\Model */
        $modelClass = $this->modelClass;
        return $modelClass::objects()->filter(array_merge(['pk' => $value], $this->extra))->get();
    }

    public function toArray()
    {
        $value = $this->getValue();
        return $value instanceof Base ? $value->pk : $value;
    }

    public function getSelectJoin(QueryBuilder $qb, $topAlias)
    {
        // TODO: Implement getSelectJoin() method.
    }
}
