<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 25/07/16
 * Time: 20:25.
 */

namespace Mindy\Orm;

use ArrayAccess;
use IteratorAggregate;
use Doctrine\DBAL\Connection;

/**
 * Class ManagerBase.
 */
abstract class ManagerBase implements ManagerInterface, IteratorAggregate, ArrayAccess
{
    /**
     * @var \Mindy\Orm\QuerySet
     */
    protected $qs;

    /**
     * @var ModelInterface
     */
    protected $model;

    /**
     * ManagerBase constructor.
     *
     * @param ModelInterface $model
     * @param Connection $connection
     * @param array $config
     */
    public function __construct(ModelInterface $model, Connection $connection, array $config = [])
    {
        $this->setModel($model);
        $this->setConnection($connection);

        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        $this->init();
    }

    protected function init()
    {
    }

    /**
     * @param QuerySet $qs
     *
     * @return $this
     */
    public function setQuerySet(QuerySet $qs)
    {
        $this->qs = $qs;

        return $this;
    }

    /**
     * @param ModelInterface $model
     *
     * @return $this
     */
    public function setModel(ModelInterface $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuerySet()
    {
        if ($this->qs === null) {
            $this->qs = new QuerySet([
                'model' => $this->getModel(),
            ]);
        }

        return $this->qs;
    }

    /**
     * {@inheritdoc}
     */
    public function setConnection($connection)
    {
        $this->getQuerySet()->setConnection($connection);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->getQuerySet()->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->getQuerySet()->all();
    }

    /**
     * {@inheritdoc}
     */
    public function limit($limit)
    {
        $this->getQuerySet()->limit($limit);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offset($offset)
    {
        $this->getQuerySet()->offset($offset);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($page, $pageSize = 10)
    {
        $this->getQuerySet()->paginate($page, $pageSize);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function order($columns)
    {
        $this->getQuerySet()->order($columns);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function count($q = '*')
    {
        return $this->getQuerySet()->count($q);
    }

    /**
     * {@inheritdoc}
     */
    public function batch($batchSize = 100)
    {
        return $this->getQuerySet()->batch($batchSize);
    }

    /**
     * {@inheritdoc}
     */
    public function each($batchSize = 100)
    {
        return $this->getQuerySet()->each($batchSize);
    }

    public function having($having)
    {
        $this->getQuerySet()->having($having);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($conditions)
    {
        $this->getQuerySet()->filter($conditions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orFilter($conditions)
    {
        $this->getQuerySet()->orFilter($conditions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function exclude($conditions)
    {
        $this->getQuerySet()->exclude($conditions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orExclude($conditions)
    {
        $this->getQuerySet()->orExclude($conditions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->getQuerySet()->getIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->getQuerySet()->offsetExists($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->getQuerySet()->offsetGet($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->getQuerySet()->offsetSet($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->getQuerySet()->offsetUnset($offset);
    }
}
