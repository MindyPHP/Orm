<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Bundle\MindyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class Controller extends BaseController
{
    /**
     * Returns a rendered view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     *
     * @return string The rendered view
     */
    protected function renderTemplate($view, array $parameters = array())
    {
        if ($this->container->has('template')) {
            return $this->container->get('template')->render($view, $parameters);
        }
        throw new \LogicException('You can not use the "renderView" method if the Template Component are not available.');
    }

    /**
     * Renders a view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     * @param Response $response
     *
     * @return string
     */
    protected function render($view, array $parameters = array(), Response $response = null)
    {
        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($this->renderTemplate($view, $parameters));
        return $response;
    }

    /**
     * Streams a view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     * @param StreamedResponse $response A response instance
     *
     * @return StreamedResponse A StreamedResponse instance
     */
    protected function stream($view, array $parameters = array(), StreamedResponse $response = null)
    {
        if ($this->container->has('template')) {
            $template = $this->container->get('template');

            $callback = function () use ($template, $view, $parameters) {
                echo $template->render($view, $parameters);
            };
        } else {
            throw new \LogicException('You can not use the "stream" method if the Templating Component are not available.');
        }

        if (null === $response) {
            return new StreamedResponse($callback);
        }

        $response->setCallback($callback);

        return $response;
    }
}