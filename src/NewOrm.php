<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 17:57
 */

namespace Mindy\Orm;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Table;
use Exception;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\QueryBuilder\QueryBuilder;

/**
 * Class NewOrm
 * @package Mindy\Orm
 */
class NewOrm extends NewBase
{
    /**
     * @return QueryBuilder
     * @throws Exception
     */
    protected function getQueryBuilder()
    {
        return QueryBuilder::getInstance($this->getConnection());
    }

    /**
     * @return \Mindy\QueryBuilder\BaseAdapter|\Mindy\QueryBuilder\Interfaces\ISQLGenerator
     */
    protected function getAdapter()
    {
        return $this->getQueryBuilder()->getAdapter();
    }

    /**
     * @param array $fields
     * @return bool
     */
    protected function updateInternal(array $fields = [])
    {
        $values = $this->getChangedAttributes($fields);
        if (empty($values)) {
            return true;
        }

        $rows = $this->objects()
            ->filter($this->getPrimaryKeyValues())
            ->update($values);

        foreach ($values as $name => $value) {
            $this->setAttribute($name, $value);
        }
        $this->updateRelated();

        return $rows >= 0;
    }

    protected function insertInternal(array $fields = [])
    {
        $dirty = $this->getDirtyAttributes();

        $values = $this->getChangedAttributes($fields);
        if (empty($values)) {
            return true;
        }

        $connection = static::getConnection();
        $qb = QueryBuilder::getInstance($connection);
        $adapter = $qb->getAdapter();

        $tableName = $adapter->quoteTableName($adapter->getRawTableName($this->tableName()));
        $inserted = $connection->executeUpdate($qb->insert($tableName, $values));
        if ($inserted === false) {
            return false;
        }

        foreach (self::getMeta()->getPrimaryKeyName(true) as $primaryKeyName) {
            if (in_array($primaryKeyName, $dirty) === false) {
                $values[$primaryKeyName] = $connection->lastInsertId($this->getSequenceName());
            }
        }

        $this->setAttributes($values);

        return true;
    }

    /**
     * @return null|string
     */
    public function getSequenceName()
    {
        $schemaManager = $this->getConnection()->getSchemaManager();

        try {
            $schemaManager->listSequences();

            return implode('_', [
                $this->tableName(),
                $this->getPrimaryKeyName(),
                'seq'
            ]);
        } catch (DBALException $e) {
            return null;
        }
    }

    /**
     * @param array $fields
     * @return bool
     * @throws Exception
     */
    public function insert(array $fields = []) : bool
    {
        $connection = $this->getConnection();

        $this->trigger('beforeInsert');

        $this->beforeInsertInternal();

        $connection->beginTransaction();
        try {
            if (($inserted = $this->insertInternal($fields))) {
                $connection->commit();
            } else {
                $connection->rollBack();
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $this->afterInsertInternal();
        $this->trigger('afterInsert');

        if ($inserted) {
            $this->setIsNewRecord(false);
            $this->updateRelated();
            $this->attributes->resetOldAttributes();
        }

        return $inserted;
    }

    /**
     * @param array $fields
     * @return bool
     * @throws Exception
     */
    public function update(array $fields = []) : bool
    {
        $connection = $this->getConnection();

        $this->trigger('beforeUpdate');

        $this->beforeUpdateInternal();

        $connection->beginTransaction();
        try {
            if ($updated = $this->updateInternal($fields)) {
                $connection->commit();
            } else {
                $connection->rollBack();
            }
        } catch (Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $this->afterUpdateInternal();
        $this->trigger('afterUpdate');

        if ($updated) {
            $this->updateRelated();
            $this->attributes->resetOldAttributes();
        }
        return $updated;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function getChangedAttributes(array $fields = []) : array
    {
        $changed = [];

        if (empty($fields)) {
            $fields = $this->getMeta()->getAttributes();
        }

        $dirty = $this->getDirtyAttributes();
        if (empty($dirty)) {
            $dirty = $fields;
        }

        foreach ($this->getPrimaryKeyValues() as $name => $value) {
            if ($value) {
                $changed[$name] = $value;
            }
        }

        $platform = $this->getConnection()->getDatabasePlatform();

        $meta = self::getMeta();
        foreach ($this->getAttributes() as $name => $attribute) {
            if (in_array($name, $fields) && in_array($name, $dirty) && $meta->hasField($name)) {
                $field = $this->getField($name);
                $sqlType = $field->getSqlType();
                if ($sqlType) {
                    $value = $field->convertToDatabaseValueSQL($attribute, $platform);
                    $changed[$name] = empty($value) ? $field->default : $value;
                }
            }
        }

        return $changed;
    }

    /**
     * @return array|Table[]
     */
    public static function createSchemaTables() : array
    {
        $columns = [];
        $indexes = [];

        $meta = self::getMeta();
        $model = self::create();

        $tables = [];
        foreach ($meta->getFields() as $name => $field) {
            $field->setModel($model);

            if ($field instanceof ManyToManyField) {
                /* @var $field \Mindy\Orm\Fields\ManyToManyField */
                if ($field->through === null) {
                    $tables[] = new Table($field->getTableName(), $field->getColumns());
                }
            } else {
                $column = $field->getColumn();
                if (empty($column)) {
                    continue;
                }

                $columns[] = $column;
                $indexes = array_merge($indexes, $field->getSqlIndexes());
            }
        }

        $table = new Table($model->tableName(), $columns, []);
        $table->setPrimaryKey($model->getPrimaryKeyName(true), 'primary');

        $tables[] = $table;

        return $tables;
    }
}