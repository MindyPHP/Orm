<?php

namespace Mindy\Orm\Fields;

use Exception;
use Mindy\Orm\HasManyManager;
use Mindy\Orm\QuerySet;

/**
 * Class HasManyField
 * @package Mindy\Orm
 */
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

    public $null = true;

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
        if (empty($this->to)) {
            $target = $this->getModel();
            $this->to = $target->normalizeTableName($target->classNameShort()) . '_' . $target->getPkName();
        }
        return $this->to;
    }

    public function from()
    {
        if (empty($this->from)) {
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

    public function onBeforeDelete()
    {
        $model = $this->getRelatedModel();
        $meta = $model->getMeta();
        $foreignField = $meta->getForeignField($this->to());
        $qs = $this->getManager()->getQuerySet();

        /**
         * If null is allowable, foreign field value should be set to null,
         * otherwise the related objects should be deleted
         */
        if ($foreignField->null) {
            $qs->update([$this->to() => null]);
        } else {
            $qs->delete();
        }
    }

    /**
     * @param $form
     * @param null $fieldClass
     * @param array $extra
     * @return \Mindy\Form\Fields\DropDownField
     */
    public function getFormField($form, $fieldClass = null, array $extra = [])
    {
        return parent::getFormField($form, \Mindy\Form\Fields\DropDownField::className(), $extra);
    }

    public function processQuerySet(QuerySet $qs, $alias, $autoGroup = true)
    {
        $grouped = false;
        list($relatedModel, $joinTables) = $this->getJoin();
        foreach ($joinTables as $join) {
            $type = isset($join['type']) ? $join['type'] : 'LEFT OUTER JOIN';
            $newAlias = $qs->makeAliasKey($join['table']);
            $table = $join['table'] . ' ' . $newAlias;

            $from = $alias . '.' . $qs->quoteColumnName($join['from']);
            $to = $newAlias . '.' . $qs->quoteColumnName($join['to']);
            $on = $qs->quoteColumnName($from) . ' = ' . $qs->quoteColumnName($to);

            $qs->join($type, $table, $on);

            // Has many relations (we must work only with current model lines - exclude duplicates)
            if ($grouped === false) {
                if ($autoGroup) {
                    $group = [];
                    if ($qs->getSchema() instanceof \Mindy\Query\Pgsql\Schema) {
                        $group[] = $newAlias . '.' . $qs->quoteColumnName($join['to']);
                    }
                    $group[] = $alias . '.' . $this->getModel()->getPkName();
                    $qs->group($group);
                }
                $qs->setChainedHasMany();
                $grouped = true;
            }

            $alias = $newAlias;
        }
        return [$relatedModel, $alias];
    }
}
