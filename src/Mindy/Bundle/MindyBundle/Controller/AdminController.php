<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/10/2016
 * Time: 20:36
 */

namespace Mindy\Bundle\MindyBundle\Controller;

use Mindy\Bundle\MindyBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AdminController extends Controller
{
    public function indexAction(Request $request)
    {
        $response = $this->render('admin/index.html', [
            'breadcrumbs' => [
                ['name' => 'Рабочий стол']
            ],
            'dashboard' => $this->get('admin.dashboard'),
            'adminMenu' => $this->get('admin.menu')->getMenu()
        ]);
        return $this->preventCache($response);
    }

    public function dispatchAction(Request $request, $bundle, $admin, $action)
    {
//        $id = $this->get('admin.registry')->resolveAdmin('product');
//        $response = $this->forward(sprintf("%s:%sAction", $id, $action), ['request' => $request]);
//        dump($response);die;
//        return $this->preventCache($response);

        /** @var \Mindy\Bundle\MindyBundle\Admin\AdminManager $adminManager */
        $response = $this->get('admin')->run($request, $bundle, $admin, $action);
        return $this->preventCache($response);
    }

    protected function preventCache(Response $response)
    {
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('max-age', 0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);

        return $response;
    }

    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/_login.html', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    public function logoutAction()
    {
    }
}