<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 09/10/2016
 * Time: 22:09
 */

namespace Mindy\Bundle\TemplateBundle\VariableProvider;

use Mindy\Component\Template\VariableProviderInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class AppVariableProvider
 * @package Mindy\Bundle\TemplateBundle\VariableProvider
 */
class AppVariableProvider implements VariableProviderInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'request' => $this->getRequest(),
            'user' => $this->getUser(),
        ];
    }

    /**
     * Get a user from the Security Token Storage.
     *
     * @return mixed
     *
     * @throws \LogicException If SecurityBundle is not available
     *
     * @see TokenInterface::getUser()
     */
    protected function getUser()
    {
        if (!$this->container->has('security.token_storage')) {
            return null;
        }

        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }
}
