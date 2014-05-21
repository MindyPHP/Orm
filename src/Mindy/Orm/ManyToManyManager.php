<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 04/01/14.01.2014 03:42
 */

namespace Mindy\Orm;

use Mindy\Exception\Exception;
use Mindy\Helper\Creator;

class ManyToManyManager extends RelatedManager
{
    /**
     * Link table name
     * @var string
     */
    public $relatedTable;
    /**
     * Main model
     * @var \Mindy\Orm\Model
     */
    public $primaryModel;

    /**
     * @var string
     */
    public $primaryModelColumn;

    /**
     * @var string
     */
    public $modelColumn;

    public function __construct(Model $model, array $config = [])
    {
        Creator::configure($this, $config);
        $this->_model = $model;
    }

    protected function escape($value)
    {
        // if has auto-quotation
        if (strpos($value, '{{') !== false || strpos($value, '`') !== false || strpos($value, '[[') !== false) {
            return $value;
        }
        return '`' . $value . '`';
    }

    // TODO: ugly, refactor me
    public function makeOnJoin($qs)
    {
        $from = $this->escape($this->relatedTable) . '.' . $this->escape($this->modelColumn);
        $to = $this->escape($qs->tableAlias) . '.' . $this->escape($this->getModel()->getPkName());
        return $from . '=' . $to;
    }

    public function getQuerySet()
    {
        if ($this->_qs === null) {
            $qs = parent::getQuerySet();
            $qs->join('JOIN',
                $this->relatedTable,
                $this->makeOnJoin($qs)
            );
            $filter = [$this->escape($this->relatedTable) . '.' . $this->escape($this->primaryModelColumn) => $this->primaryModel->pk];
            $qs = $qs->filter($filter);
            $this->_qs = $qs;
        }
        return $this->_qs;
    }

    public function clean()
    {
        if ($this->primaryModel->pk === null) {
            throw new Exception('Unable to clean models: the primary key of ' . get_class($this->primaryModel) . ' is null.');
        }

        $db = $this->primaryModel->getConnection();
        /** @var $command \Mindy\Query\Command */
        $command = $db->createCommand()->delete($this->relatedTable, [
            $this->primaryModelColumn => $this->primaryModel->pk,
        ]);

        return $command->execute();
    }

    public function link(Model $model)
    {
        return $this->linkUnlinkProcess($model, true);
    }

    public function unlink(Model $model)
    {
        return $this->linkUnlinkProcess($model, false);
    }

    protected function linkUnlinkProcess(Model $model, $link = true)
    {
        if ($this->primaryModel->pk === null) {
            throw new Exception('Unable to unlink models: the primary key of ' . get_class($this->primaryModel) . ' is null.');
        }

        $method = $link ? 'insert' : 'delete';

        if ($this->primaryModel->pk === null) {
            throw new Exception('Unable to ' . ($link ? 'link' : 'unlink') . ' models: the primary key of ' . get_class($this->primaryModel) . ' is null.');
        }

        $db = $this->primaryModel->getConnection();
        /** @var $command \Mindy\Query\Command */
        $command = $db->createCommand()->$method($this->relatedTable, [
            $this->primaryModelColumn => $this->primaryModel->pk,
            $this->modelColumn => $model->pk,
        ]);

        return $command->execute();
    }
}
