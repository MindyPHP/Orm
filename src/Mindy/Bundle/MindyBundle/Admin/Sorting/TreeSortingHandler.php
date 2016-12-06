<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 01/12/16
 * Time: 11:19
 */

namespace Mindy\Bundle\MindyBundle\Admin\Sorting;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TreeSortingHandler extends AbstractSortingHandler
{
    public function sort(Request $request, $column, array $ids)
    {
        if (false == $request->query->has('pk')) {
            throw new NotFoundHttpException("Failed to receive primary key");
        }

        $pk = $request->query->getInt('pk');

        /** @var \Mindy\Orm\TreeModel $model */
        $model = $this->getQuerySet()->get(['pk' => $pk]);
        if (null === $model) {
            throw new NotFoundHttpException("Model not found");
        }

        if ($model->getIsRoot()) {
            $roots = $this->getQuerySet()->roots()->filter(['pk__in' => $ids])->all();
            $newPositions = array_flip($ids);

            foreach ($roots as $root) {
                $descendants = $root->objects()->descendants()->filter([
                    'level__gt' => 1
                ])->valuesList(['pk'], true);

                if (count($descendants) > 0) {
                    $this->getQuerySet()->filter([
                        'pk__in' => $descendants
                    ])->update(['root' => $newPositions[$root->pk]]);
                }
            }

            foreach ($newPositions as $pk => $position) {
                $this->getQuerySet()->filter([
                    'pk' => $pk
                ])->update(['root' => $position]);
            }
        } else {
            if (isset($data['insertBefore'])) {
                $target = $this->getQuerySet()->get(['pk' => $data['insertBefore']]);
                if ($target) {
                    $model->moveBefore($target);
                }
                throw new NotFoundHttpException("Target not found");
            } else if (isset($data['insertAfter'])) {
                $target = $this->getQuerySet()->get(['pk' => $data['insertAfter']]);
                if ($target) {
                    $model->moveAfter($target);
                }
                throw new NotFoundHttpException("Target not found");
            } else {
                throw new NotFoundHttpException("Missing required parameter insertAfter or insertBefore");
            }
        }
    }
}