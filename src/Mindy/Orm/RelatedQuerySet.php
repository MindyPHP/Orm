<?php
/**
 * Created by JetBrains PhpStorm.
 * User: new
 * Date: 2/27/14
 * Time: 7:40 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Mindy\Orm;


use Mindy\Exception\Exception;

class RelatedQuerySet extends QuerySet
{

    /**
     * Link table name
     * @var \Mindy\Orm\Model
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

    public function link(Model $model)
    {
        return $this->linkUnlinkProcess($model, true);
    }

    public function unlink(Model $model)
    {
        return $this->linkUnlinkProcess($model, false);
    }

    private function linkUnlinkProcess(Model $model, $link = true)
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
