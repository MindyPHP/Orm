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
use Exception;

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
            $this->to = $this->getTable() . '_' . $this->getModel()->getPkName();
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

    public function setValue($value){
        throw new Exception("Has many field can't set values. You can do it through ForeignKey.");
    }

    public function getJoin(){
        return array($this->getRelatedModel(), array(
            array(
                'table' => $this->getRelatedTable(false),
                'from' => $this->from(),
                'to' => $this->to(),
                'group' => true
            )
        ));
    }
}
