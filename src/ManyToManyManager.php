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

    protected function linkUnlinkProcess(Model $model, $link = true, array $extra = [])
    {
        $method = $link ? 'insert' : 'delete';

        $primaryModel = $this->getPrimaryModel();
        if (($primaryModel && $primaryModel->getIsNewRecord()) || empty($primaryModel->pk)) {
            throw new Exception('Unable to ' . ($link ? 'link' : 'unlink') . ' models: the primary key of ' . get_class($primaryModel) . ' is null.');
        }

        if ($this->through && $link) {
            $throughModel = new $this->through;
            if (empty($this->throughLink)) {
                throw new Exception("throughLink is missing in ManyToManyManager");
            }
            if (empty($this->throughLink)) {
                $fromId = $this->primaryModelColumn;
                $toId = $this->modelColumn;
            } else {
                list($fromId, $toId) = $this->throughLink;
            }
            list($through, $created) = $throughModel->objects()->using($this->primaryModel->getDb())->getOrCreate([
                $fromId => $this->primaryModel->pk,
                $toId => $model->pk,
            ]);
            return $through->pk;
        } else {
            $db = $this->primaryModel->getDb();
            /** @var $command \Mindy\Query\Command */
            $command = $db->createCommand()->$method($this->relatedTable, array_merge([
                $this->primaryModelColumn => $this->primaryModel->pk,
                $this->modelColumn => $model->pk,
            ], $extra));

            return $command->execute();
        }
    }
}