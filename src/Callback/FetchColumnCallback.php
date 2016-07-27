<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 26/07/16
 * Time: 19:24
 */

namespace Mindy\Orm\Callback;

use Mindy\Orm\MetaData;
use Mindy\Orm\Model;

class FetchColumnCallback
{
    protected $model;
    protected $meta;

    public function __construct(Model $model, MetaData $meta)
    {
        $this->model = $model;
        $this->meta = $meta;
    }

    public function run($column)
    {
        if ($column === 'pk') {
            return $this->model->primaryKeyName();
        } else if ($this->meta->hasForeignField($column)) {
            return strpos($column, '_id') === false ? $column . '_id' : $column;
        } else if (strpos($column, '_id') === false) {
            $fields = $this->meta->getManyFields();
            foreach ($fields as $field) {
                if (empty($field->through) === false) {
                    $meta = MetaData::getInstance($field->through);
                    if ($meta->hasForeignField($column)) {
                        return strpos($column, '_id') === false ? $column . '_id' : $column;
                    }
                }
            }
            return $column;
        }
        return $column;
    }
}