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
 * @date 03/03/14.03.2014 19:13
 */

namespace Mindy\Orm;


use Mindy\Exception\Exception;

class LookupBuilder
{
    public $query = [];
    public $defaultLookup = 'exact';
    private $lookups = [
        'isnull',
        'lte',
        'lt',
        'gte',
        'gt',
        'exact',
        'contains',
        'icontains',
        'startswith',
        'istartswith',
        'endswith',
        'iendswith',
        'in',
        'range',
        'year',
        'month',
        'date',
        'week_day',
        'hour',
        'minute',
        'second',
        'search',
        'regex',
        'iregex'
    ];

    protected $separator = '__';

    public function __construct(array $query = [])
    {
        $this->query = $query;
    }

    public function parse()
    {
        $conditions = [];
        foreach ($this->query as $lookup => $params) {
            $conditions[] = $this->parseLookup($lookup, $params);
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
                if(!in_array($condition, $this->lookups)) {
                    $prefix[] = $field;
                    $field = $condition;
                    $condition = $this->defaultLookup;
                }
            }
            if(!in_array($condition, $this->lookups)) {
                throw new Exception("Unknown lookup operator: $condition");
            }
            return [$prefix, $field, $condition, $params];
        }
    }
}
