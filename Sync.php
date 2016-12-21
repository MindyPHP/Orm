<?php

namespace Mindy\Orm;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\QueryBuilder\QueryBuilder;

/**
 * Class Sync.
 */
class Sync
{
    /**
     * @var \Mindy\Orm\Model[]
     */
    private $_models = [];
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Sync constructor.
     *
     * @param $models
     * @param Connection $connection
     */
    public function __construct($models, Connection $connection)
    {
        if (!is_array($models)) {
            $models = [$models];
        }
        $this->_models = $models;
        $this->connection = $connection;
    }

    /**
     * @return QueryBuilder
     *
     * @throws \Exception
     */
    protected function getQueryBuilder()
    {
        return QueryBuilder::getInstance($this->connection);
    }

    /**
     * @param $model ModelInterface
     *
     * @return int
     */
    public function createTable(ModelInterface $model)
    {
        $i = 0;

        $model->setConnection($this->connection);

        $schemaManager = $this->connection->getSchemaManager();
        $adapter = $this->getQueryBuilder()->getAdapter();
        $tableName = $adapter->getRawTableName($model->tableName());

        $columns = [];
        $indexes = [];

        foreach ($model->getMeta()->getFields() as $name => $field) {
            $field->setModel($model);

            if ($field instanceof ManyToManyField) {
                /* @var $field \Mindy\Orm\Fields\ManyToManyField */
                if ($field->through === null) {
                    $fieldTableName = $adapter->getRawTableName($field->getTableName());
                    if ($this->hasTable($fieldTableName) === false) {
                        $fieldTable = new Table($adapter->quoteTableName($fieldTableName), $field->getColumns());
                        $schemaManager->createTable($fieldTable);
                        $i += 1;
                    }
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

        if ($this->hasTable($tableName) === false) {
            $table = new Table($tableName, $columns, []);
            $table->setPrimaryKey($model->getPrimaryKeyName(true), 'primary');
            $schemaManager->createTable($table);

            $i += 1;
        }

        return $i;
    }

    /**
     * @param $model ModelInterface
     *
     * @return int
     */
    public function dropTable(ModelInterface $model)
    {
        $i = 0;

        $model->setConnection($this->connection);

        $adapter = $this->getQueryBuilder()->getAdapter();

//        $this->connection->executeUpdate($adapter->sqlCheckIntegrity(false, 'public', $model->tableName()));

        $schemaManager = $this->connection->getSchemaManager();
        foreach ($model->getMeta()->getManyToManyFields() as $field) {
            if ($field->through === null) {
                $fieldTable = $adapter->getRawTableName($field->getTableName());
                if ($this->hasTable($fieldTable)) {
                    $schemaManager->dropTable($adapter->quoteTableName($fieldTable));
                    $i += 1;
                }
            }
        }

        if ($this->hasTable($model->tableName())) {
            $schemaManager->dropTable($model->tableName());
            $i += 1;
        }

//        $this->connection->executeUpdate($adapter->sqlCheckIntegrity(true, 'public', $model->tableName()));

        return $i;
    }

    /**
     * @return int
     */
    public function create()
    {
        $i = 0;
        foreach ($this->_models as $model) {
            $i += $this->createTable($model);
        }

        return $i;
    }

    /**
     * Drop all tables from database.
     *
     * @return int
     */
    public function delete()
    {
        $i = 0;
        foreach ($this->_models as $model) {
            $i += $this->dropTable($model);
        }

        return $i;
    }

    /**
     * Check table in database.
     *
     * @param null $tableName
     *
     * @return bool
     */
    public function hasTable($tableName)
    {
        if ($tableName instanceof Model) {
            $tableName = $tableName->tableName();
        }

        return $this->connection->getSchemaManager()->tablesExist([$tableName]);
    }
}
