<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     *
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
        $joinAlias = $queryBuilder->getAlias();

        $ownerModel = $this->model;
        $connection = $ownerModel->getConnection();

        reset($lookupNodes);
        $prevField = $ownerModel->getField(current($lookupNodes));
        if (!$prevField instanceof RelatedField) {
            $prevField = null;
        }

        $prevThrough = false;
        foreach ($lookupNodes as $i => $node) {
            if ($prevField instanceof RelatedField) {
                $relatedModel = $prevField->getRelatedModel();

                if ($node == 'through') {
                    $prevThrough = true;
                } else {
                    /** @var \Mindy\Orm\Fields\RelatedField $prevField */
                    if ($prevThrough && $prevField instanceof ManyToManyField) {
                        $joinAlias = $prevField
                            ->setConnection($connection)
                            ->buildThroughQuery($queryBuilder, $queryBuilder->getAlias());
                    } else {
                        $joinAlias = $prevField
                            ->setConnection($connection)
                            ->buildQuery($queryBuilder, $joinAlias);
                    }

                    if (($nextField = $relatedModel->getField($node)) instanceof RelatedField) {
                        $prevField = $nextField;
                    }
                }
            }

            if (count($lookupNodes) == $i + 1) {
                if ($lookupBuilder->hasLookup($node) === false) {
                    $column = $joinAlias.'.'.$lookupBuilder->fetchColumnName($node);
                    $columnWithLookup = $column.$lookupBuilder->getSeparator().$lookupBuilder->getDefault();
                    $queryBuilder->where([$columnWithLookup => $value]);
                } else {
                    $lookup = $node;
                    $column = $joinAlias.'.'.$lookupBuilder->fetchColumnName($lookupNodes[$i - 1]);
                }
            }
        }

        return [$lookup, $column, $value];
    }
}
