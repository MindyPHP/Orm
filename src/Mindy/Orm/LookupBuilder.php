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


class LookupBuilder
{
    public $query = [];
    public $conditions = [];
    public $defaultCondition = 'exact';

    protected $separator = '__';

    public function __construct(array $query = [])
    {
        $this->query = $query;
    }

    public function parse()
    {
        foreach ($this->query as $lookup => $params) {
            $this->conditions[] = $this->parseLookup($lookup, $params);
        }

        return $this->process();
    }

    protected function process()
    {
        $qs = null;
        foreach ($this->conditions as $data) {
            list($prefix, $field, $condition, $params) = $data;
            $this->buildQuery($prefix, $field, $condition, $params);
        }
        return $qs;
    }

    public function buildQuery($prefix, $field, $condition, $params)
    {

    }

    protected function parseLookup($lookup, array $params = [], array $prefix = [])
    {
        if (substr_count($lookup, $this->separator) >= 2) {
            $raw = explode($this->separator, $lookup);
            $prefix[] = array_shift($raw);
            return $this->parseLookup(implode($this->separator, $raw), $params, $prefix);
        } else {
            if (substr_count($lookup, $this->separator) == 0) {
                $field = $lookup;
                $condition = $this->defaultCondition;
            } else {
                list($field, $condition) = explode($this->separator, $lookup);
            }
            return [$prefix, $field, $condition, $params];
        }
    }
}
