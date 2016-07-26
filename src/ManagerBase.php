<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 25/07/16
 * Time: 20:25
 */

namespace Mindy\Orm;

abstract class ManagerBase
{
    /**
     * @var \Mindy\Orm\QuerySet
     */
    private $_qs;
    /**
     * @var \Mindy\Orm\Model
     */
    private $_model;

    public function __construct(Model $model, $config = [])
    {
        $this->_model = $model;
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }

        $this->init();
    }

    protected function init()
    {
        
    }

    /**
     * @param QuerySet $qs
     * @return $this
     */
    protected function setQuerySet(QuerySet $qs)
    {
        $this->_qs = $qs;
        return $this;
    }

    /**
     * @param Model $model
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->_model = $model;
        return $this;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @return \Mindy\Orm\QuerySet
     */
    public function getQuerySet()
    {
        if ($this->_qs === null) {
            $this->_qs = new QuerySet([
                'model' => $this->getModel()
            ]);
        }
        return $this->_qs;
    }
}