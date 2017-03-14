<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Fields;

use Exception;
use Mindy\Orm\AbstractModel;
use Mindy\Orm\ManagerInterface;
use Mindy\Orm\MetaData;
use Mindy\Orm\Model;
use Mindy\Orm\ModelInterface;
use Mindy\QueryBuilder\QueryBuilder;

/**
 * Class ManyToManyField.
 */
class ManyToManyField extends RelatedField
{
    public $null = true;

    /**
     * If to self, changes 'to' and 'from' fields.
     *
     * @var bool
     */
    public $reversed = false;
    /**
     * @var array
     */
    public $extra = [];
    /**
     * @var null|string
     */
    public $through;
    /**
     * @var array
     */
    public $link = [];
    /**
     * Related model class.
     *
     * @var string
     */
    public $modelClass;
    /**
     * Main model.
     *
     * @var \Mindy\Orm\Model
     */
    protected $_model;
    /**
     * Related model.
     *
     * @var \Mindy\Orm\Model
     */
    protected $_relatedModel;
    /**
     * Primary key name.
     *
     * @var string
     */
    protected $_modelPk;
    /**
     * Primary key name of the related model.
     *
     * @var string
     */
    protected $_relatedModelPk;
    /**
     * Model column in "link" table.
     *
     * @var string
     */
    protected $_modelColumn;
    /**
     * Related model column in "link" table.
     *
     * @var string
     */
    protected $_relatedModelColumn;
    /**
     * @var string
     */
    private $_tableName;
    /**
     * @var
     */
    protected $_columns = [];

    /**
     * @return \Mindy\Orm\Model
     */
    public function getRelatedModel()
    {
        if (!$this->_relatedModel) {
            $this->_relatedModel = new $this->modelClass();
        }

        return $this->_relatedModel;
    }

    /**
     * @return string PK name of related model
     */
    public function getRelatedModelPk()
    {
        if (!$this->_relatedModelPk) {
            $this->_relatedModelPk = $this->getRelatedModel()->getPrimaryKeyName();
        }

        return $this->_relatedModelPk;
    }

    /**
     * @return string Related model column in "link" table
     */
    public function getRelatedModelColumn()
    {
        if (!empty($this->link)) {
            list($fromId, $toId) = $this->link;

            return $toId;
        }

        if (!$this->_relatedModelColumn) {
            $cls = $this->modelClass;
            $end = $this->getRelatedModelPk();
            if ($cls == $this->ownerClassName) {
                $end = $this->reversed ? 'from_id' : 'to_id';
            }
            $tmp = explode('\\', $cls);
            $column = AbstractModel::normalizeTableName(end($tmp));
            $this->_relatedModelColumn = $column.'_'.$end;
        }

        return $this->_relatedModelColumn;
    }

    /**
     * @return string PK name of model
     */
    public function getModelPk()
    {
        if (!$this->_modelPk) {
            $this->_modelPk = MetaData::getInstance($this->ownerClassName)->getPrimaryKeyName();
        }

        return $this->_modelPk;
    }

    /**
     * @throws Exception
     *
     * @return string Model column in "link" table
     */
    public function getModelColumn()
    {
        if (empty($this->_modelColumn)) {
            if (!empty($this->through)) {
                if (empty($this->link)) {
                    $throughClass = $this->through;
                    $through = call_user_func([$throughClass, 'create']);

                    $name = '';
                    foreach ($through->getFields() as $fieldName => $params) {
                        if (isset($params['modelClass']) && $params['modelClass'] == $this->ownerClassName) {
                            $name = $fieldName;
                            break;
                        }
                    }

                    $this->_modelColumn = $name.'_id';
                } else {
                    list($fromId, $toId) = $this->link;
                    if (empty($this->link)) {
                        throw new Exception('throughLink is missing in configutaion');
                    }

                    $this->_modelColumn = $this->reversed ? $toId : $fromId;
                }
            } else {
                $cls = $this->ownerClassName;
                $end = $this->getModelPk();
                if ($cls == $this->modelClass) {
                    $end = $this->reversed ? 'to_id' : 'from_id';
                }
                $tmp = explode('\\', $cls);
                $column = AbstractModel::normalizeTableName(end($tmp));
                $this->_modelColumn = $column.'_'.$end;
            }
        }

        return $this->_modelColumn;
    }

    public function getSelectJoin(QueryBuilder $qb, $topAlias)
    {
        $throughAlias = $qb->makeAliasKey($this->getTableName());
        $alias = $qb->makeAliasKey($this->getRelatedTable());
        if (empty($this->link)) {
            $to = $this->getRelatedModelColumn();
            $from = $this->getModelColumn();
        } else {
            list($to, $from) = $this->link;
        }

        return [
            ['LEFT JOIN', $this->getTableName(), [
                $throughAlias.'.'.$from => $alias.'.'.$this->getModel()->getMeta()->getPrimaryKeyName(),
            ], $throughAlias],
        ];
    }

    public function getSelectThroughJoin(QueryBuilder $qb, $topAlias)
    {
        $throughAlias = $qb->makeAliasKey($this->getTableName());
        $alias = $qb->makeAliasKey($this->getRelatedTable());
        if (empty($this->link)) {
            $from = $this->getRelatedModelColumn();
            $to = $this->getModelColumn();
        } else {
            list($from, $to) = $this->link;
        }

        return [
            [
                'LEFT JOIN',
                $this->getTableName(),
                [
                    $throughAlias.'.'.$from => $topAlias.'.'.$this->getModel()->getPrimaryKeyName(),
                ],
                $throughAlias,
            ],
        ];
    }

    /**
     * @return ManagerInterface
     */
    public function getManager()
    {
        $className = get_class($this->getRelatedModel()->objects());
        $config = [
            'modelColumn' => $this->getRelatedModelColumn(),
            'primaryModelColumn' => $this->getModelColumn(),
            'primaryModel' => $this->getModel(),
            'relatedTable' => $this->getTableName(),
            'extra' => $this->extra,
            'through' => $this->through,
            'throughLink' => $this->link,
        ];
        /** @var \Mindy\Orm\Manager $manager */
        $manager = (new \ReflectionClass($className))->newInstanceArgs([
            $this->getRelatedModel(),
            $this->getRelatedModel()->getConnection(),
            $config,
        ]);

        if (!empty($this->link)) {
            list($from, $to) = $this->link;
            $throughAlias = $manager->getQueryBuilder()->makeAliasKey($this->getTableName());
            $this->buildSelectQuery($manager->getQueryBuilder(), $manager->getQueryBuilder()->makeAliasKey($this->getModel()->tableName()));
            if (empty($this->getModel()->pk)) {
                $manager->filter('[['.$throughAlias.']].[['.$from.']] IS NULL');
            } else {
                $manager->filter('[['.$throughAlias.']].[['.$from.']]=@'.$this->getModel()->pk.'@');
            }
        } else {
            $from = $this->getRelatedModelColumn();
            $to = $this->getModelColumn();

            $throughAlias = $manager->getQueryBuilder()->makeAliasKey($this->getTableName());
            $this->buildSelectQuery($manager->getQueryBuilder(), $manager->getQueryBuilder()->makeAliasKey($this->getModel()->tableName()));
            if (empty($this->getModel()->pk)) {
                $manager->filter('[['.$throughAlias.']].[['.$from.']] IS NULL');
            } else {
                $manager->filter('[['.$throughAlias.']].[['.$from.']]=@'.$this->getModel()->pk.'@');
            }
        }

        if (!empty($this->extra)) {
            $manager->filter($this->extra);
        }

        return $manager;
    }

    private $_build_through = false;

    public function buildThroughQuery(QueryBuilder $qb, $topAlias)
    {
        $this->_build_through = true;
        $joinAlias = '???';
        foreach ($this->getSelectThroughJoin($qb, $topAlias) as $join) {
            list($joinType, $tableName, $on, $alias) = $join;
            $qb->join($joinType, $tableName, $on, $alias);
            $joinAlias = $alias;
        }

        return $joinAlias;
    }

    /**
     * Table name of the "link" table.
     *
     * @return string
     */
    public function getTableName()
    {
        if (!$this->through) {
            $adapter = QueryBuilder::getInstance($this->getConnection())->getAdapter();
            $parts = [$adapter->getRawTableName($this->getTable()), $adapter->getRawTableName($this->getRelatedTable())];
            sort($parts);

            return '{{%'.implode('_', $parts).'}}';
        }

        return call_user_func([$this->through, 'tableName']);
    }

    /**
     * @return array "link" table columns
     */
    public function getColumns()
    {
        if (empty($this->link)) {
            $from = $this->getRelatedModelColumn();
            $to = $this->getModelColumn();
        } else {
            list($from, $to) = $this->link;
        }

        return [
            (new IntField(['name' => $from]))->getColumn(),
            (new IntField(['name' => $to]))->getColumn(),
        ];
    }

    /**
     * @return bool|string
     */
    public function getSqlType()
    {
        return false;
    }

    protected function preformatValue($value)
    {
        if (empty($value)) {
            return [];
        }

        if (!is_array($value)) {
            if (is_string($value) && strpos($value, ',') !== false) {
                $value = explode(',', $value);
            } else {
                $value = [$value];
            }
        }

        if (is_array($value) && count($value) > 0) {
            if (
                is_numeric($value[0]) ||
                $value[0] instanceof Model
            ) {
                return $value;
            }
            if (empty($value[0])) {
                return [];
            }

            throw new Exception('ManyToMany field can set only arrays of Models or existing primary keys');
        }

        return [];
    }

    /**
     * @param array $value
     *
     * @throws \Exception
     */
    public function setValue($value)
    {
        $value = $this->preformatValue($value);
        $manager = $this->getManager();
        $manager->clean();
        foreach ($value as $linkModel) {
            if (
                ($linkModel instanceof ModelInterface) === false &&
                !is_a($linkModel, $this->modelClass)) {
                $linkModel = call_user_func([$this->modelClass, 'objects'])->get(['pk' => $linkModel]);
            }

            if ($linkModel instanceof ModelInterface) {
                $manager->link($linkModel);
            } else {
                throw new Exception('ManyToMany field can set only arrays of Models or existing primary keys');
            }
        }
    }

    protected function getThroughTableName()
    {
        if ($this->through) {
            return call_user_func([$this->through, 'tableName']);
        }
    }

    public function getJoin(QueryBuilder $qb, $topAlias)
    {
        $relatedModel = $this->getRelatedModel();
        $throughAlias = $qb->makeAliasKey($this->getTableName());
        $alias = $qb->makeAliasKey($this->getRelatedTable());
        if (empty($this->link)) {
            $to = $this->getRelatedModelColumn();
            $from = $this->getModelColumn();
        } else {
            list($to, $from) = $this->link;
        }

        return [
            ['LEFT JOIN', $this->getTableName(), [
                $throughAlias.'.'.$to => $topAlias.'.'.$relatedModel->getPrimaryKeyName(),
            ], $throughAlias],

            ['LEFT JOIN', $this->getRelatedTable(), [
                $alias.'.'.$this->getModel()->getPrimaryKeyName() => $throughAlias.'.'.$from,
            ], $alias],
        ];
    }

    public function fetch($value)
    {
        // TODO: Implement fetch() method.
    }

    public function getAttributeName()
    {
        return false;
    }

    public function getValue()
    {
        return $this->getManager();
    }
}
