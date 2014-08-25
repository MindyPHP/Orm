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

use Exception;
use Mindy\Form\Fields\DropDownField;
use Mindy\Orm\HasManyManager;

class HasManyField extends RelatedField
{
    /**
     * @var array extra condition for join
     */
    public $extra = [];
    /**
     * @var \Mindy\Orm\Model
     */
    protected $_relatedModel;

    /**
     * @var \Mindy\Orm\Model
     */
    protected $_model;

    /**
     * TODO: docs
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

    public $through;

    public function init()
    {

    }

    public function sqlType()
    {
        return false;
    }

    public function getManager()
    {
        return new HasManyManager($this->getRelatedModel(), [
            'primaryModel' => $this->getModel(),
            'from' => $this->from(),
            'to' => $this->to(),
            'extra' => $this->extra,
            'through' => $this->through
        ]);
    }

    public function to()
    {
        if (!$this->to) {
            $this->to = $this->getModel()->getPkName();
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

    public function setValue($value)
    {
        throw new Exception("Has many field can't set values. You can do it through ForeignKey.");
    }

    public function getJoin()
    {
        return [$this->getRelatedModel(), [[
            'table' => $this->getRelatedTable(false),
            'from' => $this->from(),
            'to' => $this->to(),
            'group' => true
        ]]];
    }

    public function fetch($value)
    {
        return;
    }

    public function getFormField($form, $fieldClass = null)
    {
        return parent::getFormField($form, DropDownField::className());
    }
}
