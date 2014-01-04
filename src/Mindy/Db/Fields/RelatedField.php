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
 * @date 03/01/14.01.2014 22:02
 */

namespace Mindy\Db\Fields;


use Mindy\Db\Model;
use Mindy\Db\Relation;

abstract class RelatedField extends IntField
{
    /**
     * @var \Mindy\Db\Relation
     */
    private $_relation;

    /**
     * @var string
     */
    public $relatedName;

    /**
     * @var \Mindy\Db\Model
     */
    private $_model;

    public function getRelation()
    {
        return $this->_relation;
    }

    public function setRelation(Relation $relation)
    {
        return $this->_relation = $relation;
    }

    /**
     * Creates an [[ActiveRelation]] instance.
     * This method is called by [[hasOne()]] and [[hasMany()]] to create a relation instance.
     * You may override this method to return a customized relation.
     * @param array $config the configuration passed to the ActiveRelation class.
     * @return \Mindy\Db\Relation the newly created [[ActiveRelation]] instance.
     */
    public function createRelation(array $config)
    {
        return new Relation($config);
    }

    public function setModel(Model $model)
    {
        $this->_model = $model;
    }

    public function getModel()
    {
        return $this->_model;
    }
}
