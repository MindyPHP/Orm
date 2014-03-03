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

namespace Mindy\Orm\Fields;

use Mindy\Orm\HasManyManager;
use Mindy\Orm\Model;

class HasManyField extends RelatedField
{
    /**
     * @var \Mindy\Orm\Model
     */
    protected $_relatedModel;

    /**
     * @var \Mindy\Orm\Model
     */
    protected $_model;

    /**
     * TODO: Write normal doc
     * Explain by example: model User has many models Pages
     * User->id <- from
     * Pages->user_id <- to
     * @var string
     */
    public $from;

    /**
     * @var string
     */
    public $to;

    public $modelClass;

    public function init()
    {

    }

    /**
     * @param \Mindy\Orm\Model $model
     */
    public function setModel(Model $model)
    {
        $this->_model = $model;
    }

    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @return \Mindy\Orm\Model
     */
    public function getRelatedModel()
    {
        if (!$this->_relatedModel) {
            $this->_relatedModel = new $this->modelClass();
        }
        return $this->_relatedModel;
    }

    public function sqlType()
    {
        return false;
    }

    public function getManager()
    {
        $manager = new HasManyManager($this->getRelatedModel(), [
            'primaryModel' => $this->getModel(),
            'from' => $this->from(),
            'to' => $this->to()
        ]);

        return $manager;
    }

    public function to()
    {
        if (!$this->to) {
            $this->to = $this->getModel()->tableName() . '_' . $this->getModel()->getPkName();
        }
        return $this->to;
    }

    public function from()
    {
        if (!$this->from) {
            $this->from = $this->getModel()->getPkName();
        }
        return $this->from;
    }
}
