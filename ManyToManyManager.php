<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm;

use Exception;
use Mindy\QueryBuilder\QueryBuilderFactory;
use Mindy\QueryBuilder\Utils\TableNameResolver;

/**
 * Class ManyToManyManager.
 */
abstract class ManyToManyManager extends ManagerBase
{
    /**
     * Main model.
     *
     * @var \Mindy\Orm\Model
     */
    public $primaryModel;
    /**
     * @var null|string
     */
    public $through;
    /**
     * @var array
     */
    public $throughLink = [];
    /**
     * @var string
     */
    public $primaryModelColumn;
    /**
     * @var string
     */
    public $modelColumn;
    /**
     * Link table name.
     *
     * @var string
     */
    public $relatedTable;

    /**
     * @param ModelInterface $model
     * @param array          $extra
     *
     * @throws Exception
     *
     * @return int
     */
    public function link(ModelInterface $model, array $extra = [])
    {
        return $this->linkUnlinkProcess($model, true, $extra);
    }

    /**
     * @param ModelInterface $model
     *
     * @throws Exception
     *
     * @return int
     */
    public function unlink(ModelInterface $model)
    {
        return $this->linkUnlinkProcess($model, false);
    }

    /**
     * @return Model
     */
    private function getPrimaryModel()
    {
        return $this->primaryModel;
    }

    /**
     * @throws Exception
     *
     * @return int
     */
    public function clean()
    {
        if (null === $this->primaryModel->pk) {
            throw new Exception('Unable to clean models: the primary key of '.get_class($this->primaryModel).' is null.');
        }
        $db = $this->primaryModel->getConnection();
        $builder = QueryBuilderFactory::getQueryBuilder($db);

        return $db->delete($builder->getQuotedName(TableNameResolver::getTableName($this->relatedTable)), [$this->primaryModelColumn => $this->primaryModel->pk]);
    }

    /**
     * @param ModelInterface $model
     * @param bool           $link
     * @param array          $extra
     *
     * @throws Exception
     *
     * @return int
     */
    protected function linkUnlinkProcess(ModelInterface $model, $link = true, array $extra = [])
    {
        $primaryModel = $this->getPrimaryModel();
        if ($primaryModel && empty($primaryModel->pk)) {
            throw new Exception('Unable to '.($link ? 'link' : 'unlink').' models: the primary key of '.get_class($primaryModel).' is '.$primaryModel->pk.'.');
        }

        if ($this->through && $link) {
            /** @var \Mindy\Orm\Model $throughModel */
            $throughModel = new $this->through();
            if (empty($this->throughLink)) {
                $from = $this->primaryModelColumn;
                $to = $this->modelColumn;
            } else {
                list($from, $to) = $this->throughLink;
            }
            list($through, $created) = $throughModel->objects()->getOrCreate([
                $from => $this->primaryModel->pk,
                $to => $model->pk,
            ]);

            return $through->pk;
        }
        $db = $this->primaryModel->getConnection();
        $builder = QueryBuilderFactory::getQueryBuilder($db);
        $data = array_merge([
                $this->primaryModelColumn => $this->primaryModel->pk,
                $this->modelColumn => $model->pk,
            ], $extra);
        if ($link) {
            $state = $model->getConnection()->insert(
                $builder->getQuotedName(TableNameResolver::getTableName($this->relatedTable)),
                $data
            );
        } else {
            $state = $model->getConnection()->delete(
                $builder->getQuotedName(TableNameResolver::getTableName($this->relatedTable)),
                $data
            );
        }

        return $state;
    }
}
