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

namespace Mindy\Orm;


use Mindy\Helper\Creator;

class HasManyManager extends RelatedManager{

    /**
     * Primary model (has many owner)
     * @var \Mindy\Orm\Model
     */
    public $primaryModel;

    /**
     * @var string
     */
    public $from;

    /**
     * @var string
     */
    public $to;

    public function __construct(Model $model, array $config = [])
    {
        Creator::configure($this, $config);
        $this->_model = $model;
    }

    public function getQuerySet(){
        if($this->_qs === null) {
            $qs = parent::getQuerySet();
            $this->_qs = $qs->filter([$this->to => $this->primaryModel->{$this->from}]);
        }
        return $this->_qs;
    }
}