<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Fields;

use Exception;
use Mindy\Orm\Manager;
use Mindy\Orm\ManagerInterface;
use Mindy\Orm\Model;
use Mindy\QueryBuilder\QueryBuilder;

/**
 * Class HasManyField.
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
     * Pages->user_id <- to.
     *
     * @var string
     */
    public $from;

    /**
     * @var string
     */
    public $to;

    public $modelClass;

    public $through;
    /**
     * @var array
     */
    public $link = [];

    public $null = true;

    public function getSqlType()
    {
        return false;
    }

    /**
     * @return ManagerInterface
     */
    public function getManager()
    {
        list($from, $to) = $this->link;

        $manager = new Manager($this->getRelatedModel(), $this->getModel()->getConnection());
        $manager->filter(array_merge([$from => $this->getModel()->getAttribute($to)], $this->extra));

        if ($this->getModel()->getIsNewRecord()) {
            $manager->distinct();
        }

        return $manager;
    }

    public function setValue($value)
    {
        throw new Exception("Has many field can't set values. You can do it through ForeignKey.");
    }

    public function getJoin(QueryBuilder $qb, $topAlias)
    {
        $tableName = $this->getRelatedTable();
        $alias = $qb->makeAliasKey($tableName);
        list($from, $to) = $this->link;

        return [
            ['LEFT JOIN', $tableName, [$alias.'.'.$from => $topAlias.'.'.$to], $alias],
        ];
    }

    protected function getTo()
    {
        return $this->getModel()->getMeta()->getPrimaryKeyName();
    }

    protected function getFrom()
    {
        return implode('_', [
            $this->getModel()->tableName(),
            $this->getRelatedModel()->getMeta()->getPrimaryKeyName(),
        ]);
    }

    public function fetch($value)
    {
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

    public function getSelectJoin(QueryBuilder $qb, $topAlias)
    {
        // TODO: Implement getSelectJoin() method.
    }

    public function getAttributeName()
    {
        return false;
    }

    public function getValue()
    {
        return $this->getManager();
    }
}
