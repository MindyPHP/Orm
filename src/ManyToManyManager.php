<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 24/07/16
 * Time: 13:19
 */

namespace Mindy\Orm;

use Mindy\Exception\Exception;

abstract class ManyToManyManager extends ManagerBase
{
    /**
     * Main model
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
     * Link table name
     * @var string
     */
    public $relatedTable;

    public function link(Model $model, array $extra = [])
    {
        return $this->linkUnlinkProcess($model, true, $extra);
    }

    public function unlink(Model $model)
    {
        return $this->linkUnlinkProcess($model, false);
    }

    private function getPrimaryModel()
    {
        return $this->primaryModel;
    }

    public function clean()
    {
        if ($this->primaryModel->pk === null) {
            throw new Exception('Unable to clean models: the primary key of ' . get_class($this->primaryModel) . ' is null.');
        }
        $db = $this->primaryModel->getDb();
        $builder = $db->getQueryBuilder()->setTypeDelete()->from($this->relatedTable)->where([
            $this->primaryModelColumn => $this->primaryModel->pk,
        ]);
        return $db->createCommand($builder->toSQL())->execute();
    }

    protected function linkUnlinkProcess(Model $model, $link = true, array $extra = [])
    {
        $primaryModel = $this->getPrimaryModel();
        if (($primaryModel && $primaryModel->getIsNewRecord()) || empty($primaryModel->pk)) {
            throw new Exception('Unable to ' . ($link ? 'link' : 'unlink') . ' models: the primary key of ' . get_class($primaryModel) . ' is null.');
        }

        if ($this->through && $link) {
            /** @var \Mindy\Orm\Model $throughModel */
            $throughModel = new $this->through;
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
        } else {
            $db = $this->primaryModel->getDb();
            /** @var $command \Mindy\Query\Command */
            $builder = $db->getQueryBuilder();
            $data = array_merge([
                $this->primaryModelColumn => $this->primaryModel->pk,
                $this->modelColumn => $model->pk,
            ], $extra);
            if ($link) {
                $sql = $builder->insert($this->relatedTable, array_keys($data), [$data]);
            } else {
                $sql = $builder->setTypeDelete()->from($this->relatedTable)->where($data);
            }

            return $db->createCommand($sql)->execute();
        }
    }
}