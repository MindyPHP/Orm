<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 04/10/16
 * Time: 21:05
 */

namespace Mindy\Component\Application;

/**
 * Class LegacyMethodsTrait
 * @package Mindy\Traits
 * @method \Symfony\Component\DependencyInjection\ContainerInterface getContainer()
 */
trait LegacyMethodsTrait
{
    public function hasComponent($id)
    {
        return $this->getContainer()->has($id);
    }

    public function getComponent($id)
    {
        return $this->getContainer()->get($id);
    }

    public function getUser()
    {
        if (!$this->getContainer()->has('security.token_storage')) {
            return null;
        }

        if (null === $token = $this->getContainer()->get('security.token_storage')->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }
}
