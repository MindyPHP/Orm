<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Exception;
use Mindy\Orm\ManagerInterface;
use Mindy\Orm\ModelInterface;
use Mindy\QueryBuilder\QueryBuilder;

/**
 * Class ForeignField.
 */
class ForeignField extends RelatedField
{
    public $onDelete;

    public $onUpdate;

    public $modelClass;

    public $extra = [];

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
        return call_user_func([$this->modelClass, 'getPkName']);
    }

    public function getJoin(QueryBuilder $qb, $topAlias)
    {
        $alias = $qb->makeAliasKey($this->getRelatedModel()->tableName());

        return [
            [
                'LEFT JOIN',
                $this->getRelatedTable(false),
                [$topAlias.'.'.$this->name.'_id' => $alias.'.'.$this->getRelatedModel()->getPrimaryKeyName()],
                $alias,
            ],
        ];
    }

    /**
     * @param $value
     *
     * @throws Exception
     *
     * @return \Mindy\Orm\Model|\Mindy\Orm\TreeModel|null
     */
    protected function fetch($value)
    {
        if (empty($value)) {
            if ($this->null === true) {
                return;
            }
            throw new Exception('Value in fetch method of PrimaryKeyField cannot be empty');
        }

        return $this->fetchModel($value);
    }

    protected function fetchModel($value)
    {
        return $this->getManager()->get(array_merge(['pk' => $value], $this->extra));
    }

    public function toArray()
    {
        $value = $this->getValue();
        if ($value instanceof ModelInterface) {
            return $value->pk;
        }

        return $value;
    }

    public function getSelectJoin(QueryBuilder $qb, $topAlias)
    {
        // TODO: Implement getSelectJoin() method.
    }

    /**
     * @return string
     */
    public function getAttributeName()
    {
        return $this->name.'_id';
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof ModelInterface) {
            return $value;
        } elseif (!is_null($value)) {
            return $this->fetchModel($value);
        }

        return $value;
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     */
    public function convertToPHPValueSQL($value, AbstractPlatform $platform)
    {
        if ($value instanceof ModelInterface) {
            return $value->pk;
        }

        return $value;
    }

    public function setValue($value)
    {
        if ($value instanceof ModelInterface) {
            $value = $value->pk;
        }
        parent::setValue($value);
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     *
     * @return int|string
     */
    public function convertToDatabaseValueSql($value, AbstractPlatform $platform)
    {
        return parent::convertToDatabaseValueSQL($value instanceof ModelInterface ? $value->pk : $value, $platform);
    }

    /**
     * @return ManagerInterface
     */
    public function getManager()
    {
        return call_user_func([$this->modelClass, 'objects']);
    }
}
