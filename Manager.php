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
use Mindy\QueryBuilder\Exception\NotSupportedException;
use Mindy\QueryBuilder\QueryBuilderInterface;

/**
 * Class Manager.
 */
class Manager extends ManyToManyManager
{
    /**
     * @param string|array $value
     *
     * @return $this
     */
    public function with($value)
    {
        $this
            ->getQuerySet()
            ->with($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function asArray($value = true)
    {
        $this
            ->getQuerySet()
            ->asArray($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function select($columns, $option = null)
    {
        $this
            ->getQuerySet()
            ->select($columns, $option);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($conditions = [])
    {
        return $this->getQuerySet()->get($conditions);
    }

    /**
     * @param $rows
     *
     * @return Model[]
     */
    public function createModels($rows)
    {
        return $this->getQuerySet()->createModels($rows);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws NotSupportedException
     *
     * @return string
     */
    public function allSql(): string
    {
        return $this->getQuerySet()->allSql();
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws NotSupportedException
     *
     * @return string
     */
    public function countSql(): string
    {
        return $this->getQuerySet()->countSql();
    }

    /**
     * {@inheritdoc}
     */
    public function average($q)
    {
        return $this->getQuerySet()->average($q);
    }

    /**
     * @param $q
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws NotSupportedException
     *
     * @return string
     */
    public function averageSql($q): string
    {
        return $this->getQuerySet()->averageSql($q);
    }

    /**
     * {@inheritdoc}
     */
    public function min($q)
    {
        return $this->getQuerySet()->min($q);
    }

    /**
     * @param $q
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws NotSupportedException
     *
     * @return string
     */
    public function minSql($q): string
    {
        return $this->getQuerySet()->minSql($q);
    }

    /**
     * {@inheritdoc}
     */
    public function max($q)
    {
        return $this->getQuerySet()->max($q);
    }

    /**
     * @param $q
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws NotSupportedException
     *
     * @return string
     */
    public function maxSql($q): string
    {
        return $this->getQuerySet()->maxSql($q);
    }

    /**
     * {@inheritdoc}
     */
    public function sum($q)
    {
        return $this->getQuerySet()->sum($q);
    }

    /**
     * @param $q
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws NotSupportedException
     *
     * @return string
     */
    public function sumSql($q): string
    {
        return $this->getQuerySet()->sumSql($q);
    }

    /**
     * @param $q
     * @param bool $flat
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return array
     */
    public function valuesList($q, $flat = false): array
    {
        return $this->getQuerySet()->valuesList($q, $flat);
    }

    /**
     * Get model if exists. Else create model.
     *
     * @param array $attributes
     *
     * @throws Exception\MultipleObjectsReturned
     *
     * @return array
     */
    public function getOrCreate(array $attributes)
    {
        return $this->getQuerySet()->getOrCreate($attributes);
    }

    /**
     * @param string $sql
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array
     */
    public function raw(string $sql)
    {
        return $this->getQuerySet()->raw($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function updateOrCreate(array $attributes, array $updateAttributes)
    {
        return $this->getQuerySet()->updateOrCreate($attributes, $updateAttributes);
    }

    /**
     * @param array $attributes
     *
     * @return int
     */
    public function update(array $attributes)
    {
        return $this->getQuerySet()->update($attributes);
    }

    /**
     * @param array $attributes
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws NotSupportedException
     *
     * @return string
     */
    public function updateSql(array $attributes): string
    {
        return $this
            ->getQuerySet()
            ->updateSql($attributes);
    }

    /**
     * @param array $attributes
     *
     * @return bool
     */
    public function delete(array $attributes = [])
    {
        return $this
            ->filter($attributes)
            ->getQuerySet()
            ->delete();
    }

    /**
     * @param array $attributes
     *
     * @throws DBALException
     * @throws NotSupportedException
     *
     * @return string
     */
    public function deleteSql(array $attributes = []): string
    {
        return $this
            ->filter($attributes)
            ->getQuerySet()
            ->deleteSql();
    }

    /**
     * @param array $attributes
     *
     * @return bool
     */
    public function create(array $attributes): bool
    {
        $model = $this->getModel();
        $model->setAttributes($attributes);

        return $model->save();
    }

    /**
     * @throws DBALException
     * @throws NotSupportedException
     *
     * @return int
     */
    public function truncate()
    {
        return $this->getQuerySet()->truncate();
    }

    /**
     * {@inheritdoc}
     */
    public function getSql($conditions = [])
    {
        return $this->getQuerySet()->getSql($conditions);
    }

    /**
     * {@inheritdoc}
     */
    public function distinct($fields = true)
    {
        return $this->getQuerySet()->distinct($fields);
    }

    /**
     * {@inheritdoc}
     */
    public function group($fields)
    {
        $this->getQuerySet()->group($fields);

        return $this;
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return $this->getQuerySet()->getTableAlias();
    }

    /**
     * @throws NotSupportedException
     *
     * @return \Mindy\QueryBuilder\QueryBuilderInterface
     */
    public function getQueryBuilder(): QueryBuilderInterface
    {
        return $this->getQuerySet()->getQueryBuilder();
    }
}
