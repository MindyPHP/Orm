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
 * @date 17/04/14.04.2014 14:15
 */

namespace Mindy\Orm;


use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\IntField;

abstract class TreeModel extends Model
{
    public function getFields()
    {
        return [
            'parent' => ['class' => ForeignField::className(), 'modelClass' => get_class($this)],
            'lft' => ['class' => IntField::className()],
            'rgt' => ['class' => IntField::className()],
            'level' => ['class' => IntField::className()],
            'root' => ['class' => IntField::className()],
        ];
    }

    public static function objects()
    {
        $className = get_called_class();
        return new TreeManager(new $className);
    }

    /**
     * Determines if node is leaf.
     * @return boolean whether the node is leaf.
     */
    public function getIsLeaf()
    {
        return $this->rgt - $this->lft === 1;
    }

    /**
     * Determines if node is root.
     * @return boolean whether the node is root.
     */
    public function getIsRoot()
    {
        return $this->lft == 1;
    }
}
