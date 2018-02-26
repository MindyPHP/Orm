<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm;

use Doctrine\DBAL\DBALException;
use Exception;
use Mindy\QueryBuilder\QueryBuilder;
use Mindy\QueryBuilder\QueryBuilderFactory;
use Mindy\QueryBuilder\Utils\TableNameResolver;

class AbstractModel extends Base
{
    /**
     * @throws Exception
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        return QueryBuilderFactory::getQueryBuilder($this->getConnection());
    }

    /**
     * @param array $fields
     *
     * @return bool
     *
     * @throws Exception
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

    /**
     * @param array $fields
     *
     * @return bool
     *
     * @throws DBALException
     * @throws Exception
     */
    protected function insertInternal(array $fields = [])
    {
        $dirty = $this->getDirtyAttributes();

        $values = $this->getChangedAttributes($fields);
        if (empty($values)) {
            return true;
        }

        $connection = static::getConnection();
        $qb = $this->getQueryBuilder();

        $tableName = $qb->getQuotedName(TableNameResolver::getTableName($this->tableName()));
        $sql = $qb
            ->insert()
            ->table($tableName)
            ->values($values)
            ->toSQL();
        $inserted = $connection->executeUpdate($sql);
        if (false === $inserted) {
            return false;
        }

        foreach (self::getMeta()->getPrimaryKeyName(true) as $primaryKeyName) {
            if (
                empty($this->getAttribute($this->getSequenceName())) ||
                false === in_array($primaryKeyName, $dirty)
            ) {
                $values[$primaryKeyName] = $connection->lastInsertId($this->getSequenceName());
            }
        }

        $this->setAttributes($values);

        return true;
    }

    /**
     * @return null|string
     *
     * @throws Exception
     */
    public function getSequenceName()
    {
        $schemaManager = $this->getConnection()->getSchemaManager();

        try {
            $schemaManager->listSequences();

            return implode('_', [
                $this->tableName(),
                $this->getPrimaryKeyName(),
                'seq',
            ]);
        } catch (DBALException $e) {
            return;
        }
    }

    /**
     * @param array $fields
     *
     * @throws Exception
     *
     * @return bool
     */
    public function insert(array $fields = [])
    {
        $connection = $this->getConnection();

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

        if ($inserted) {
            $this->setIsNewRecord(false);
            $this->updateRelated();
            $this->attributes->resetOldAttributes();
        }

        return $inserted;
    }

    /**
     * @param array $fields
     *
     * @throws Exception
     *
     * @return bool
     */
    public function update(array $fields = [])
    {
        $connection = $this->getConnection();

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

        if ($updated) {
            $this->updateRelated();
            $this->attributes->resetOldAttributes();
        }

        return $updated;
    }

    /**
     * @param array $fields
     *
     * @return array
     *
     * @throws Exception
     */
    public function getChangedAttributes(array $fields = [])
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
                    $value = $field->convertToDatabaseValue($attribute, $platform);
                    $changed[$name] = null === $value ? $field->default : $value;
                }
            }
        }

        return $changed;
    }
}
