<?php

namespace Mindy\Orm;

use DateTime;
use Mindy\Exception\Exception;
use Mindy\Query\QueryBuilder;

/**
 * Class LookupBuilder
 * @package Mindy\Orm
 */
class LookupBuilder
{
    /**
     * @var array
     */
    public $query = [];
    /**
     * @var string
     */
    public $defaultLookup = 'exact';
    /**
     * @var array
     */
    private $lookups = [
        'isnull', 'lte', 'lt', 'gte',
        'gt', 'exact', 'contains', 'icontains',
        'startswith', 'istartswith', 'endswith',
        'iendswith', 'in', 'range', 'year', 'month',
        'day', 'week_day', 'hour', 'minute', 'second',
        'search', 'regex', 'iregex'
    ];

    protected $separator = '__';

    public function __construct(array $query = [])
    {
        $this->query = $query;
    }

    protected function prepareParams(QueryBuilder $qb, $params)
    {
        if (is_array($params)) {
            $newParams = [];
            foreach ($params as $key => $param) {
                if ($param instanceof DateTime) {
                    $newParams[$key] = $param->format($qb->dateTimeFormat);
                } else {
                    $newParams[$key] = $param;
                }
            }
        } else {
            $newParams = $params instanceof DateTime ? $params->format($qb->dateTimeFormat) : $params;
        }

        return $newParams;
    }

    public function parse(QueryBuilder $qb)
    {
        $conditions = [];
        foreach ($this->query as $lookup => $params) {
            $newParams = $this->prepareParams($qb, $params);
            $conditions[] = $this->parseLookup($lookup, $newParams);
        }
        return $conditions;
    }

    public function parseLookup($lookup, $params = [], array $prefix = [])
    {
        $raw = explode($this->separator, $lookup);
        $first = array_shift($raw);
        if (substr_count($lookup, $this->separator) >= 2 && !in_array($first, $this->lookups)) {
            $prefix[] = $first;
            return $this->parseLookup(implode($this->separator, $raw), $params, $prefix);
        } else {
            if (substr_count($lookup, $this->separator) == 0) {
                $field = $lookup;
                $condition = $this->defaultLookup;
            } else {
                list($field, $condition) = explode($this->separator, $lookup);
                if (!in_array($condition, $this->lookups)) {
                    $prefix[] = $field;
                    $field = $condition;
                    $condition = $this->defaultLookup;
                }
            }
            if (!in_array($condition, $this->lookups)) {
                throw new Exception("Unknown lookup operator: $condition");
            }
            return [$prefix, $field, $condition, $params];
        }
    }
}
