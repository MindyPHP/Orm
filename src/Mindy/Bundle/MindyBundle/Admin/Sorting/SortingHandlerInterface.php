<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 01/12/16
 * Time: 11:18
 */

namespace Mindy\Bundle\MindyBundle\Admin\Sorting;

use Symfony\Component\HttpFoundation\Request;

interface SortingHandlerInterface
{
    public function sort(Request $request, $column, array $ids);
}