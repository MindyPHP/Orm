<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 13/11/2016
 * Time: 21:06
 */

namespace Mindy\Bundle\MindyBundle\Admin;

use Mindy\Bundle\MindyBundle\Admin\Sorting\SortingHandler;
use Mindy\Bundle\MindyBundle\Admin\Sorting\TreeSortingHandler;
use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\TextField;
use Mindy\Orm\ModelInterface;
use Mindy\Orm\TreeManager;
use Mindy\Orm\TreeModel;
use Mindy\Orm\TreeQuerySet;
use Mindy\Pagination\Pagination;
use Mindy\QueryBuilder\Q\QOr;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class AbstractModelAdmin extends AbstractAdmin
{
    /**
     * @var array
     */
    public $permissions = [
        'create' => true,
        'update' => true,
        'info' => true,
        'remove' => true
    ];

    public $pager = [
        'pageSize' => 50
    ];

    public $sorting = null;

    public $linkColumn = null;

    public $columns = null;

    public $defaultOrder = null;

    public $searchFields = null;

    protected $fetcher;

    protected $sortingHandler;

    protected $propertyAccessor;

    public function __construct(AdminTemplateFinder $templateFinder)
    {
        parent::__construct($templateFinder);

        $this->fetcher = new AdminValueFetcher();
        $this->propertyAccessor = new PropertyAccessor();

        $instance = (new \ReflectionClass($this->getModelClass()))->newInstance();
        if ($instance instanceof TreeModel) {
            $columns = [];
            if (null === $this->defaultOrder) {
                $columns = ['root', 'lft'];
            } else if (is_array($this->defaultOrder)) {
                $columns = array_merge(['root', 'lft'], $columns);
            } else {
                $columns = [$this->defaultOrder];
            }
            $this->defaultOrder = $columns;

            $this->sortingHandler = new TreeSortingHandler($instance);
        } else {
            $this->sortingHandler = new SortingHandler($instance);
        }
    }

    public function getFilterFormType()
    {
        return null;
    }

    public function getSearchFields()
    {
        if (null === $this->searchFields) {
            $fields = [];
            foreach (call_user_func([$this->getModelClass(), 'getMeta'])->getFields() as $name => $field) {
                if ($field instanceof CharField || $field instanceof TextField) {
                    $fields[] = $name;
                }
            }
            return $fields;
        }

        return $this->searchFields;
    }

    /**
     * @param ModelInterface $model
     * @return array
     */
    public function getInfoFields(ModelInterface $model)
    {
        return $model->getMeta()->getAttributes();
    }

    /**
     * @param $code
     * @return bool
     */
    public function can($code)
    {
        static $defaultPermissions = [
            'create' => true,
            'update' => true,
            'info' => true,
            'remove' => true
        ];
        $permissions = array_merge($defaultPermissions, $this->permissions);
        return isset($permissions[$code]) && $permissions[$code];
    }

    abstract public function getFormType();

    abstract public function getModelClass();

    public function getOrderUrl(Request $request, $column)
    {
        $order = $request->query->get('order', '');
        if ($order == $column) {
            $column = '-' . $column;
        }
        $queryString = http_build_query(array_merge($request->query->all(), ['order' => $column]));
        return strtok($request->getUri(), '?') . '?' . $queryString;
    }

    public function getVerboseNames()
    {
        return [];
    }

    /**
     * @param $column
     * @return mixed
     */
    public function getVerboseName($column)
    {
        $model = (new \ReflectionClass($this->getModelClass()))->newInstance();
        $names = $this->getVerboseNames();
        if (isset($names[$column])) {
            return $names[$column];
        } else if ($model->hasField($column)) {
            return $model->getField($column)->getVerboseName();
        } else {
            return $column;
        }
    }

    /**
     * Render table cell. Used in template list.html
     * @param $column
     * @param ModelInterface $model
     * @return string
     */
    public function renderCell($column, ModelInterface $model)
    {
        $value = $this->fetcher->fetchValue($column, $model);

        if ($template = $this->findTemplate('columns/_' . $column . '.html', false)) {
            return $this->renderTemplate($template, [
                'admin' => $this,
                'model' => $model,
                'column' => $column,
                'value' => $value
            ]);
        } else {
            return $value;
        }
    }

    public function getColumns()
    {
        if (null === $this->columns) {
            $fields = call_user_func([$this->getModelClass(), 'getMeta'])->getFields();
            return array_keys($fields);
        }
        return $this->columns;
    }

    /**
     * Get qs from model.
     * @return \Mindy\Orm\Manager|\Mindy\Orm\TreeManager
     */
    public function getQuerySet()
    {
        return call_user_func([$this->getModelClass(), 'objects']);
    }

    /**
     * @param \Mindy\Orm\QuerySet|\Mindy\Orm\Manager $qs
     * @param string $q
     * @param array $fields
     */
    public function applySearchToQuerySet($qs, $q, array $fields)
    {
        if (empty($q)) {
            return;
        }

        $filters = [];
        foreach ($fields as $field) {
            $lookup = 'icontains';
            $fieldName = $field;
            if (strpos($field, '=') === 0) {
                $fieldName = substr($field, 1);
                $lookup = 'exact';
            }

            $filters[] = [$fieldName . '__' . $lookup => $q];
        }
        $qs->filter([new QOr($filters)]);
    }

    protected function isTree($qs)
    {
        return $qs instanceof TreeManager || $qs instanceof TreeQuerySet;
    }

    public function sortingAction(Request $request)
    {
        $qs = $this->getQuerySet();

        $tree = $this->isTree($qs);
        $ids = $request->query->get('models', []);
        if ($tree) {
            $this->sortingHandler->sort($request, null, $ids);
        } else {
            $this->sortingHandler->sort($request, $this->sorting, $ids);
        }

        $this->prepareQuerySet($request, $qs);
        $pager = new Pagination($qs, $this->pager);

        return $this->render($this->findTemplate('_table.html'), [
            'models' => $pager->paginate(),
            'pager' => $pager,
            'tree' => $tree,
            'sorting' => $this->sorting || $tree,
            'columns' => $this->getColumns()
        ]);
    }

    protected function prepareQuerySet(Request $request, $qs)
    {
        if ($this->isTree($qs)) {
            if ($request->query->has('parent_id') && ($pk = $request->query->getInt('parent_id'))) {
                $clone = clone $qs;
                $parent = $clone->get(['pk' => $pk]);
                $qs->filter(['parent_id' => $pk]);

                if (null === $parent) {
                    throw new NotFoundHttpException;
                }
            } else {
                $qs->roots();
            }
        }

        if ($request->query->has('order')) {
            $qs->order([
                $request->query->get('order')
            ]);
        } else if (null !== $this->defaultOrder) {
            $qs->order($this->defaultOrder);
        } else if ($this->sorting) {
            $qs->order($this->sorting);
        }

        if ($request->query->has('search')) {
            $this->applySearchToQuerySet($qs, $request->query->get('search'), $this->getSearchFields());
        }
    }

    protected function processFilter($qs, array $data)
    {

    }

    public function listAction(Request $request)
    {
        $qs = $this->getQuerySet();

        $tree = $this->isTree($qs);
        $this->prepareQuerySet($request, $qs);

        $pager = new Pagination($qs, $this->pager);

        $instance = (new \ReflectionClass($this->getModelClass()))->newInstance();

        $filterFormView = null;
        if ($filterFormType = $this->getFilterFormType()) {
            $filterForm = $this->createForm($filterFormType, $request->query->get('filter', []), [
                'method' => 'GET'
            ]);
            $filterFormView = $filterForm->createView();

            if ($filterForm->handleRequest($request)) {
                $this->processFilter($qs, $filterForm->getData());
            }
        }

        return $this->render($this->findTemplate('list.html'), [
            'pager' => $pager,
            'tree' => $tree,
            'filterForm' => $filterFormView,
            'linkColumn' => $this->linkColumn,
            'sorting' => $this->sorting || $tree,
            'columns' => $this->getColumns(),
            'models' => $pager->paginate(),
            'breadcrumbs' => $this->fetchBreadcrumbs($request, $instance, 'list')
        ]);
    }

    /**
     * @param Request $request
     * @param ModelInterface $model
     * @param string $action
     * @return array
     */
    public function getCustomBreadrumbs(Request $request, ModelInterface $model, string $action)
    {
        if ($model instanceof TreeModel) {
            $pk = $request->query->get('pk');
            if (!empty($pk)) {
                /** @var null|TreeModel $instance */
                $instance = call_user_func([$this->getModelClass(), 'objects'])->get(['pk' => $pk]);
                if ($instance) {
                    return $this->getParentBreadcrumbs($instance);
                }
            }
        }

        return [];
    }

    /**
     * @param $model
     * @return array
     */
    public function getParentBreadcrumbs(TreeModel $model)
    {
        $parents = [];

        if ($model->pk) {
            $parents = $model->objects()->ancestors()->order(['lft'])->all();
            $parents[] = $model;
        }

        $breadcrumbs = [];
        foreach ($parents as $parent) {
            $breadcrumbs[] = [
                'url' => $this->getAdminUrl('list', ['parent_id' => $parent->pk]),
                'name' => (string)$parent,
                'items' => []
            ];
        }
        return $breadcrumbs;
    }

    /**
     * @param Request $request
     * @param ModelInterface $model
     * @param $action
     * @return array
     */
    public function fetchBreadcrumbs(Request $request, ModelInterface $model, $action)
    {
        list($list, $create, $update) = $this->getAdminNames($model);
        $breadcrumbs = [
            ['name' => $list, 'url' => $this->getAdminUrl('list')]
        ];
        $custom = $this->getCustomBreadrumbs($request, $model, $action);
        if (!empty($custom)) {
            // Fetch user custom breadcrumbs
            $breadcrumbs = array_merge($breadcrumbs, $custom);
        }

        $bundleName = $this->bundle->getName();
        switch ($action) {
            case 'create':
                $breadcrumbs[] = ['name' => $create];
                break;
            case 'update':
                $breadcrumbs[] = ['name' => $update];
                break;
            case 'list':
                break;
            case 'info':
                $breadcrumbs[] = [
                    'name' => $this->get('translator')->trans('admin.breadcrumbs.info', ['%name%' => (string)$model], sprintf('%s.admin', $bundleName)),
                    'url' => $this->getAdminUrl('list')
                ];
                break;
            default:
                break;
        }

        return $breadcrumbs;
    }

    /**
     * Array of action => name, where actions is an
     * action in this admin class
     * @return array
     */
    public function getActions()
    {
        return $this->can('remove') ? [
            'batchRemove' => $this->get('translator')->trans('admin.actions.batch_remove'),
        ] : [];
    }

    /**
     * @param ModelInterface|null $instance
     * @return array
     */
    public function getAdminNames(ModelInterface $instance = null)
    {
        $bundleName = strtolower(str_replace('Bundle', '', $this->bundle->getName()));
        $model = str_replace(' ', '_', TextHelper::normalizeName(TextHelper::shortName($this->getModelClass())));
        $trans = $this->get('translator');
        return [
            $trans->trans(sprintf('%s.admin.%s.list', $bundleName, $model)),
            $trans->trans(sprintf('%s.admin.%s.create', $bundleName, $model)),
            $trans->trans(sprintf('%s.admin.%s.update', $bundleName, $model), ['%name%' => (string)$instance]),
        ];
    }

    public function infoAction(Request $request)
    {
        $instance = call_user_func([$this->getModelClass(), 'objects'])->get([
            'pk' => $request->query->get('pk')
        ]);

        if (null === $instance) {
            throw new NotFoundHttpException();
        }

        $fields = [];
        foreach ($this->getInfoFields($instance) as $fieldName) {
            $fields[$fieldName] = $instance->getField($fieldName);
        }

        return $this->render($this->findTemplate('info.html'), [
            'model' => $instance,
            'fields' => $fields,
            'breadcrumbs' => $this->fetchBreadcrumbs($request, $instance, 'info')
        ]);
    }

    public function printAction(Request $request)
    {
        $instance = call_user_func([$this->getModelClass(), 'objects'])->get([
            'pk' => $request->query->get('pk')
        ]);

        if (null === $instance) {
            throw new NotFoundHttpException();
        }

        $fields = [];
        foreach ($this->getInfoFields($instance) as $fieldName) {
            $fields[$fieldName] = $instance->getField($fieldName);
        }

        return $this->render($this->findTemplate('info_print.html'), [
            'model' => $instance,
            'fields' => $fields,
            'breadcrumbs' => $this->fetchBreadcrumbs($request, $instance, 'info')
        ]);
    }

    protected function beforeCreate($instance)
    {

    }

    protected function beforeUpdate($instance)
    {

    }

    public function createAction(Request $request)
    {
        $instance = (new \ReflectionClass($this->getModelClass()))->newInstance();

        $form = $this->createForm($this->getFormType(), $instance, [
            'method' => 'POST',
            'attr' => ['enctype' => 'multipart/form-data']
        ]);

        if ($request->getMethod() === 'POST') {
            if ($form->handleRequest($request)->isValid()) {
                $instance = $form->getData();
                $this->beforeCreate($instance);

                if ($instance->save()) {
                    $this->addFlash(self::FLASH_SUCCESS, $this->get('translator')->trans('admin.flash.success'));

                    return $this->redirect($this->getAdminUrl('update', ['pk' => $instance->id]));
                }
            }
        }

        return $this->render($this->findTemplate('create.html'), [
            'form' => $form->createView(),
            'breadcrumbs' => $this->fetchBreadcrumbs($request, $instance, 'create')
        ]);
    }

    /**
     * Example usage:
     *
     * switch ($action) {
     *      case "save_create":
     *          return ['parent' => 'parent_id'];
     *      case "save":
     *          return ['parent' => 'pk'];
     *      default:
     *          return [];
     * }
     *
     * @param $action
     * @return array
     */
    public function getRedirectParams($action)
    {
        return [];
    }

    /**
     * Collect correct array for redirect
     * @param array $attributes
     * @param $action
     * @return array
     */
    protected function fetchRedirectParams(array $attributes, $action)
    {
        $redirectParams = [];
        $saveParams = $this->getRedirectParams($action);
        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $saveParams)) {
                $redirectParams[$saveParams[$key]] = $value;
            }
        }
        return $redirectParams;
    }

    /**
     * @param array $data
     * @param ModelInterface $model
     * @return string url for redirect
     */
    public function getNextRoute(array $data, ModelInterface $model)
    {
        if (array_key_exists('save_continue', $data)) {
            return $this->getAdminUrl('update', array_merge($this->fetchRedirectParams($model->getAttributes(), 'save_continue'), ['pk' => $model->pk]));
        } else if (array_key_exists('save_create', $data)) {
            return $this->getAdminUrl('create', $this->fetchRedirectParams($model->getAttributes(), 'save_create'));
        } else {
            return $this->getAdminUrl('list', $this->fetchRedirectParams($model->getAttributes(), 'save'));
        }
    }

    public function updateAction(Request $request)
    {
        $instance = call_user_func([$this->getModelClass(), 'objects'])->get([
            'pk' => $request->query->get('pk')
        ]);
        if ($instance === null) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm($this->getFormType(), $instance, [
            'method' => 'POST',
            'attr' => ['enctype' => 'multipart/form-data']
        ]);

        if ($request->getMethod() === 'POST') {
            if ($form->handleRequest($request)->isValid()) {
                $instance = $form->getData();
                $this->beforeUpdate($instance);

                if ($instance->save()) {
                    $this->addFlash(self::FLASH_SUCCESS, $this->get('translator')->trans('admin.flash.success'));

                    return $this->redirect($request->getRequestUri());
                }
            }
        }

        return $this->render($this->findTemplate('update.html'), [
            'form' => $form->createView(),
            'instance' => $instance,
            'breadcrumbs' => $this->fetchBreadcrumbs($request, $instance, 'update')
        ]);
    }

    public function removeAction(Request $request)
    {
        $instance = call_user_func([$this->getModelClass(), 'objects'])->get([
            'pk' => $request->query->get('pk')
        ]);
        if ($instance === null) {
            throw new NotFoundHttpException();
        }

        if ($instance->delete()) {
            return $this->redirect($this->getAdminUrl('list'));
        }

        throw new \RuntimeException('Failed to remove entry');
    }

    public function getAbsoluteUrl(ModelInterface $model)
    {
        if (method_exists($model, 'getAbsoluteUrl')) {
            return $model->getAbsoluteUrl();
        }

        return null;
    }
}