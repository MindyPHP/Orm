<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 24/07/16
 * Time: 13:19.
 */

namespace Mindy\Orm;

use Exception;
use Mindy\QueryBuilder\QueryBuilder;

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
     * @param Model $model
     * @param array $extra
     *
     * @return int
     */
    public function link(Model $model, array $extra = [])
    {
        return $this->linkUnlinkProcess($model, true, $extra);
    }

    /**
     * @param Model $model
     *
     * @return int
     */
    public function unlink(Model $model)
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
     * @return int
     *
     * @throws Exception
     */
    public function clean()
    {
        if ($this->primaryModel->pk === null) {
            throw new Exception('Unable to clean models: the primary key of '.get_class($this->primaryModel).' is null.');
        }
        $db = $this->primaryModel->getConnection();
        $adapter = QueryBuilder::getInstance($db)->getAdapter();

        return $db->delete($adapter->quoteTableName($adapter->getRawTableName($this->relatedTable)), [$this->primaryModelColumn => $this->primaryModel->pk]);
    }

    /**
     * @param Model $model
     * @param bool  $link
     * @param array $extra
     *
     * @return int
     *
     * @throws Exception
     */
    protected function linkUnlinkProcess(Model $model, $link = true, array $extra = [])
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
        } else {
            $db = $this->primaryModel->getConnection();
            $builder = QueryBuilder::getInstance($db);
            $data = array_merge([
                $this->primaryModelColumn => $this->primaryModel->pk,
                $this->modelColumn => $model->pk,
            ], $extra);
            $adapter = $builder->getAdapter();
            if ($link) {
                $state = $model->getConnection()->insert($adapter->quoteTableName($adapter->getRawTableName($this->relatedTable)), $data);
            } else {
                $state = $model->getConnection()->delete($adapter->quoteTableName($adapter->getRawTableName($this->relatedTable)), $data);
            }

            return $state;
        }
    }
}
