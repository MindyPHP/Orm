<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 01/12/16
 * Time: 11:16
 */

namespace Mindy\Bundle\MindyBundle\Admin\Sorting;

use Symfony\Component\HttpFoundation\Request;

class SortingHandler extends AbstractSortingHandler
{
    public function sort(Request $request, $column, array $ids)
    {
        /**
         * Pager-independent sorting
         */
        $oldPositions = $this->getQuerySet()->filter(['pk__in' => $ids])
            ->valuesList([$column], true);
        asort($oldPositions);

        foreach ($ids as $id) {
            $this->getQuerySet()
                ->filter(['pk' => $id])
                ->update([
                    $column => array_shift($oldPositions)
                ]);
        }
    }
}