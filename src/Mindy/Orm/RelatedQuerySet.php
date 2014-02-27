<?php
/**
 * Created by JetBrains PhpStorm.
 * User: new
 * Date: 2/27/14
 * Time: 7:40 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Mindy\Orm;


class RelatedQuerySet extends QuerySet{

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
        if ($this->primaryModel->pk === null) {
            throw new Exception('Unable to link models: the primary key of ' . get_class($this->primaryModel) . ' is null.');
        }

        $command = $this->primaryModel->getConnection()->createCommand()->insert($this->relatedTable, [
            $this->primaryModelColumn => $this->primaryModel->pk,
            $this->modelColumn => $model->pk,
        ]);
        return $command->execute();
    }

    public function unlink(Model $model)
    {
        if ($this->primaryModel->pk === null) {
            throw new Exception('Unable to link models: the primary key of ' . get_class($this->primaryModel) . ' is null.');
        }

        $command = $this->primaryModel->getConnection()->createCommand()->delete($this->relatedTable,[
            $this->primaryModelColumn => $this->primaryModel->pk,
            $this->modelColumn => $model->pk,
        ]);
        return $command->execute();
    }
}