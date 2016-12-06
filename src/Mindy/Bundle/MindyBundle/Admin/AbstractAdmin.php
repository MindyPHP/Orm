<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 13/11/2016
 * Time: 20:53
 */

namespace Mindy\Bundle\MindyBundle\Admin;

use Mindy\Bundle\MindyBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractAdmin extends Controller implements AdminInterface
{
    use BundleAwareTrait;

    const FLASH_SUCCESS = 'admin_success';
    const FLASH_NOTICE = 'admin_notice';
    const FLASH_WARNING = 'admin_warning';
    const FLASH_ERROR = 'admin_error';

    protected $templateFinder;

    /**
     * BaseAdmin constructor.
     * @param AdminTemplateFinder $templateFinder
     */
    public function __construct(AdminTemplateFinder $templateFinder)
    {
        $this->templateFinder = $templateFinder;
    }

    /**
     * @return string
     */
    public function classNameShort() : string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * @param $action
     * @param array $params
     * @return string
     */
    public function getAdminUrl($action, array $params = [])
    {
        return $this->generateUrl('admin_dispatch', array_merge($params, [
            'bundle' => $this->bundle->getName(),
            'admin' => $this->classNameShort(),
            'action' => $action
        ]));
    }

    /**
     * @param $template
     * @param bool $throw
     * @return null|string
     */
    public function findTemplate($template, $throw = true)
    {
        $template = $this->templateFinder->findTemplate($this->bundle->getName(), $this->classNameShort(), $template);
        if (null === $template && $throw) {
            throw new \RuntimeException(sprintf('Template %s not found', $template));
        }
        return $template;
    }

    /**
     * @param string $view
     * @param array $parameters
     * @param Response $response
     * @return string
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        return parent::render($view, array_merge($parameters, [
            'admin' => $this,
            'bundle' => $this->bundle,
            'adminMenu' => $this->container->get('admin.menu')->getMenu()
        ]), $response);
    }
}