<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 01/12/16
 * Time: 11:23
 */

namespace Mindy\Bundle\MindyBundle\Admin\Sorting;

use Mindy\Orm\ModelInterface;

abstract class AbstractSortingHandler implements SortingHandlerInterface
{
    protected $model;

    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
    }

    public function getQuerySet()
    {
        return $this->model->objects();
    }
}