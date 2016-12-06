<?php

namespace Mindy\Bundle\MindyBundle\Admin;

use Mindy\Orm\ModelInterface;
use function Mindy\trans;
use Mindy\Bundle\MindyBundle\Model\Menu;
use Symfony\Component\HttpFoundation\Request;

class MenuAdmin extends AbstractModelAdmin
{
    /**
     * @var string
     */
    public $treeLinkColumn = 'name';

    public function getCustomBreadrumbs(Request $request, ModelInterface $model, string $action)
    {
        $breadcrumbs = [];
        if ($model->getIsNewRecord()) {
            if ($parentId = $request->query->get('parent_id')) {
                $model = Menu::objects()->get(['id' => $parentId]);
                if ($model === null) {
                    return [];
                }
            } else {
                return [];
            }
        }

        $parents = $model->objects()->ancestors(true)->order(['lft'])->all();
        foreach ($parents as $ancestor) {
            $breadcrumbs[] = [
                'name' => (string)$ancestor,
                'url' => $this->getAdminUrl('list') . '?' . http_build_query(['pk' => $ancestor->id])
            ];
        }

        return $breadcrumbs;
    }

    public function getSearchFields()
    {
        return ['name'];
    }

    public function getColumns()
    {
        return ['name', 'slug', 'url'];
    }

    public function getModelClass()
    {
        return Menu::class;
    }

    public function getFormType()
    {
        // TODO: Implement getFormType() method.
    }
}
