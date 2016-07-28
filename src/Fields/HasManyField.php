<?php

namespace Mindy\Orm\Fields;

use Exception;
use Mindy\Orm\HasManyManager;
use Mindy\Orm\Model;
use Mindy\Orm\QuerySet;
use Mindy\QueryBuilder\QueryBuilder;

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
            'from' => $this->getTo(),
            'to' => $this->getFrom(),
            'extra' => $this->extra,
            'through' => $this->through
        ]);
    }

    public function setValue($value)
    {
        throw new Exception("Has many field can't set values. You can do it through ForeignKey.");
    }

    public function getJoin(QueryBuilder $qb, $topAlias)
    {
        $tableName = $this->getRelatedTable();
        $alias = $qb->makeAliasKey($tableName);

        return [
            ['LEFT JOIN', $tableName, [$alias . '.' . $this->getFrom() => $topAlias . '.' . $this->getTo()], $alias]
        ];
    }

    protected function getTo()
    {
        return $this->getModel()->getPkName();
    }

    protected function getFrom()
    {
        $model = $this->getModel();
        $related = $this->getRelatedModel();
        return Model::normalizeTableName($model->classNameShort()) . '_' . $related->getPkName();
    }

    public function fetch($value)
    {
        return;
    }

    public function onBeforeDelete()
    {
        /*
        $model = $this->getRelatedModel();
        $meta = $model->getMeta();
        $foreignField = $meta->getForeignField($this->getTo());
        $qs = $this->getManager()->getQuerySet();

        // If null is allowable, foreign field value should be set to null, otherwise the related objects should be deleted
        if ($foreignField->null) {
            $qs->update([$this->getTo() => null]);
        } else {
            $qs->delete();
        }
        */
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

    public function getSelectJoin(QueryBuilder $qb, $topAlias)
    {
        // TODO: Implement getSelectJoin() method.
    }
}
