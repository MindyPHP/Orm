<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 26/07/16
 * Time: 19:29
 */

namespace Mindy\Orm\Callback;

use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Fields\RelatedField;
use Mindy\Orm\Model;
use Mindy\Orm\ModelInterface;
use Mindy\QueryBuilder\LookupBuilder\LookupBuilder;
use Mindy\QueryBuilder\QueryBuilder;

class LookupCallback
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * LookupCallback constructor.
     * @param Model $model
     */
    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
    }

    public function run(QueryBuilder $queryBuilder, LookupBuilder $lookupBuilder, array $lookupNodes, $value)
    {
        $lookup = $lookupBuilder->getDefault();
        $column = '';
        $joinAlias = '';
        $alias = $queryBuilder->getAlias();

        $prevField = null;
        foreach ($lookupNodes as $i => $node) {
            if ($node == 'through' && $prevField && $prevField instanceof ManyToManyField) {
                $joinAlias = $prevField->setConnection($this->model->getConnection())->buildThroughQuery($queryBuilder, $queryBuilder->getAlias());
            } else if ($this->model->hasField($node) && ($field = $this->model->getField($node)) instanceof RelatedField) {
                $prevField = $field;
                /** @var \Mindy\Orm\Fields\RelatedField $field */
                $joinAlias = $field->setConnection($this->model->getConnection())->buildQuery($queryBuilder, $alias);
            } else if ($prevField) {
                /** @var \Mindy\Orm\Fields\RelatedField $prevField */
                $model = $prevField->getRelatedModel();
                if ($model->hasField($node) && ($field = $model->getField($node)) instanceof RelatedField) {
                    /** @var \Mindy\Orm\Fields\RelatedField $field */
                    $joinAlias = $field->setConnection($this->model->getConnection())->buildQuery($queryBuilder, $joinAlias);
                }
            }

            if (count($lookupNodes) == $i + 1) {
                if ($lookupBuilder->hasLookup($node) === false) {
                    $column = $joinAlias . '.' . $lookupBuilder->fetchColumnName($node);
                    $columnWithLookup = $column . $lookupBuilder->getSeparator() . $lookupBuilder->getDefault();
                    $queryBuilder->where([$columnWithLookup => $value]);
                } else {
                    $lookup = $node;
                    $column = $joinAlias . '.' . $lookupBuilder->fetchColumnName($lookupNodes[$i - 1]);
                }
            }
        }

        return [$lookup, $column, $value];
    }
}