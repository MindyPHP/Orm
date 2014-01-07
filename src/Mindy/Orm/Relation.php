<?php

/**
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 03/01/14.01.2014 22:03
 */

namespace Mindy\Orm;


use Mindy\Query\ActiveRelation;
use Exception;

class Relation extends ActiveRelation
{
    public function link(Model $model)
    {
        if(!$this->multiple) {
            throw new Exception("Relation must be a multiple");
        }

        if ($this->primaryModel->pk === null) {
            throw new Exception('Unable to link models: the primary key of ' . get_class($this->primaryModel) . ' is null.');
        }

        $primaryTableName = $this->primaryModel->tableName();
        $primaryPk = $this->primaryModel->primaryKey();
        $tableName = $model->tableName();
        $pk = $model->primaryKey();

        $command = $this->primaryModel->getConnection()->createCommand()->insert($primaryTableName . '_' . $tableName, [
            $primaryTableName . '_id' => $primaryPk[0],
            $tableName . '_id' => $pk[0],
        ]);
        return $command->execute();
    }

    public function unlink(Model $model)
    {
        if(!$this->multiple) {
            throw new Exception("Relation must be a multiple");
        }
        $primaryTableName = strtolower($this->primaryModel->className());
        $tableName = strtolower($model->className());

        $command = $this->primaryModel->getConnection()->createCommand()->delete($primaryTableName . '_' . $tableName,
            $primaryTableName . '=:primaryPk AND ' . $tableName . '=:pk',
            [
                'primaryPk' => $this->primaryModel->pk,
                'pk' => $model->pk,
            ]);
        return $command->execute();
    }
}
