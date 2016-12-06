<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 01/10/2016
 * Time: 18:53
 */

namespace Mindy\Bundle\MindyBundle\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class AdminManager
 * @package Mindy\Admin
 */
class AdminManager
{
    protected $kernel;
    protected $templateFinder;

    /**
     * AdminManager constructor.
     * @param Kernel $kernel
     * @param AdminTemplateFinder $templateFinder
     */
    public function __construct(Kernel $kernel, AdminTemplateFinder $templateFinder)
    {
        $this->kernel = $kernel;
        $this->templateFinder = $templateFinder;
    }

    /**
     * @param string $bundleName
     * @param string $admin
     * @return AdminInterface
     */
    public function createAdmin(string $bundleName, string $admin) : AdminInterface
    {
        $bundles = $this->kernel->getBundles();
        if (!array_key_exists($bundleName, $bundles)) {
            throw new NotFoundHttpException(sprintf(
                "Bundle not found: %", $bundleName
            ));
        }

        try {
            $bundle = $this->kernel->getBundle($bundleName);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $adminClass = sprintf("%s\\Admin\\%s", $bundle->getNamespace(), $admin);
        if (class_exists($adminClass)) {
            return $this->initiateAdmin($bundle, $adminClass);
        }

        throw new NotFoundHttpException("Admin class not found");
    }

    /**
     * @param Bundle $bundle
     * @param string $adminClass
     * @return Admin
     */
    protected function initiateAdmin(Bundle $bundle, string $adminClass)
    {
        /** @var Admin $instance */
        $instance = (new \ReflectionClass($adminClass))->newInstanceArgs([
            $this->templateFinder
        ]);
        $instance->setBundle($bundle);
        $instance->setContainer($this->kernel->getContainer());
        return $instance;
    }

    /**
     * @param Request $request
     * @param $bundle
     * @param $admin
     * @param $action
     * @return Response
     */
    public function run(Request $request, $bundle, $admin, $action)
    {
        $admin = $this->createAdmin($bundle, $admin);
        $method = sprintf('%sAction', $action);

        if (method_exists($admin, $method)) {
            return call_user_func_array([$admin, $method], [$request]);
        }

        throw new NotFoundHttpException();
    }
}