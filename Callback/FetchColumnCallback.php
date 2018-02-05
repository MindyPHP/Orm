<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Callback;

use Mindy\Orm\MetaData;
use Mindy\Orm\ModelInterface;

class FetchColumnCallback
{
    protected $model;
    protected $meta;

    public function __construct(ModelInterface $model, MetaData $meta)
    {
        $this->model = $model;
        $this->meta = $meta;
    }

    public function run($column)
    {
        if ('pk' === $column) {
            return $this->model->getPrimaryKeyName();
        } elseif ($this->meta->hasForeignField($column)) {
            return false === strpos($column, '_id') ? $column.'_id' : $column;
        } elseif (false === strpos($column, '_id')) {
            $fields = $this->meta->getManyToManyFields();
            foreach ($fields as $field) {
                if (false === empty($field->through)) {
                    $meta = MetaData::getInstance($field->through);
                    if ($meta->hasForeignField($column)) {
                        return false === strpos($column, '_id') ? $column.'_id' : $column;
                    }
                }
            }

            return $column;
        }

        return $column;
    }
}
