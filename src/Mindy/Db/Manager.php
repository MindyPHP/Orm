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

namespace Mindy\Db;


use Exception;

class Manager extends Query
{
    /**
     * @var \Mindy\Db\Model
     */
    private $_model;

    public function __construct(Model $model)
    {
        parent::__construct(['modelClass' => $model->className()]);

        $this->setModel($model);
    }

    /**
     * @param Model $model
     */
    public function setModel(Model $model)
    {
        $this->_model = $model;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * Returns the primary key name(s) for this AR class.
     * The default implementation will return the primary key(s) as declared
     * in the DB table that is associated with this AR class.
     *
     * If the DB table does not declare any primary key, you should override
     * this method to return the attributes that you want to use as primary keys
     * for this AR class.
     *
     * Note that an array should be returned even for a table with single primary key.
     *
     * @return string[] the primary keys of the associated database table.
     */
    public function primaryKey()
    {
        return $this->getModel()->getTableSchema()->primaryKey;
    }

    public function filter($q = null)
    {
        if (is_array($q)) {
            return $this->andWhere($q);
        } elseif ($q !== null) {
            // query by primary key
            $primaryKey = $this->primaryKey();
            if (isset($primaryKey[0])) {
                return $this->andWhere([$primaryKey[0] => $q]);
            } else {
                throw new Exception(get_called_class() . ' must have a primary key.');
            }
        }
        return $this;
    }
}
