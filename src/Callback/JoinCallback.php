<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 26/07/16
 * Time: 19:32
 */

namespace Mindy\Orm\Callback;

use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Fields\RelatedField;
use Mindy\Orm\Model;
use Mindy\QueryBuilder\LookupBuilder\Legacy;
use Mindy\QueryBuilder\QueryBuilder;

class JoinCallback
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function run(QueryBuilder $queryBuilder, Legacy $lookupBuilder, array $lookupNodes)
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
                    $alias = $prevField->setDb($this->model->getDb())->buildThroughQuery($queryBuilder, $queryBuilder->getAlias());
                } else if ($this->model->hasField($nodeName)) {
                    $field = $this->model->getField($nodeName);
                    if ($field instanceof RelatedField) {
                        /** @var \Mindy\Orm\Fields\RelatedField $field */
                        $alias = $field->setDb($this->model->getDb())->buildQuery($queryBuilder, $queryBuilder->getAlias());
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