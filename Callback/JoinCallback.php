<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 26/07/16
 * Time: 19:32.
 */

namespace Mindy\Orm\Callback;

use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Fields\RelatedField;
use Mindy\Orm\ModelInterface;
use Mindy\QueryBuilder\LookupBuilder\LookupBuilder;
use Mindy\QueryBuilder\QueryBuilder;

class JoinCallback
{
    protected $model;

    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
    }

    public function run(QueryBuilder $queryBuilder, LookupBuilder $lookupBuilder, array $lookupNodes)
    {
        $column = '';
        $alias = '';
        /** @var \Mindy\Orm\Fields\RelatedField|null $prevField */
        $prevField = null;
        foreach ($lookupNodes as $i => $nodeName) {
            if ($i + 1 == count($lookupNodes)) {
                $column = $nodeName;
            } else {
                if ($nodeName == 'through' && $prevField && $prevField instanceof ManyToManyField) {
                    $alias = $prevField->setConnection($this->model->getConnection())->buildThroughQuery($queryBuilder, $queryBuilder->getAlias());
                } elseif ($this->model->hasField($nodeName)) {
                    $field = $this->model->getField($nodeName);
                    if ($field instanceof RelatedField) {
                        /* @var \Mindy\Orm\Fields\RelatedField $field */
                        $alias = $field->setConnection($this->model->getConnection())->buildQuery($queryBuilder, $queryBuilder->getAlias());
                        $prevField = $field;
                    }
                }
            }
        }

        if (empty($alias) || empty($column)) {
            return false;
        }

        return [$alias, $column];
    }
}
