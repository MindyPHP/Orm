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

namespace Mindy\Orm\Fields;

use \Mindy\Exception\Exception;

abstract class RelatedField extends IntField
{
    /**
     * @var string
     */
    public $relatedName;
    public $modelClass;

    protected $_model;
    protected $_relatedModel;

    public function getJoin(){
        throw new Exception('Not implemented');
    }

    /**
     * @return \Mindy\Orm\Model
     */
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

    public function getTable($clean = true){
        $tableName = $this->getModel()->tableName();
        return $clean ? $this->cleanTableName($tableName) : $tableName;
    }

    public function getRelatedTable($clean = true){
        $tableName = $this->getRelatedModel()->tableName();
        return $clean ? $this->cleanTableName($tableName) : $tableName;
    }

    public function cleanTableName($tableName){
        if ($tableName){
            return str_replace(['{{','}}','%','`'],'',$tableName);
        };
        return '';
    }
}
