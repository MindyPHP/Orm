<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 09/10/2016
 * Time: 23:50
 */

namespace Mindy\Bundle\MindyBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class ResponseListener
{
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        if ((($result = $event->getControllerResult()) instanceof Request) === false) {
            if (is_string($result)) {
                $event->setResponse(new Response($result));

            } else if (is_array($result)) {
                $event->setResponse(new JsonResponse($result));

            } else if (is_object($result)) {

                if (method_exists($result, 'toArray')) {
                    $event->setResponse(new JsonResponse($result->toArray()));
                } else if (method_exists($result, 'toJson')) {
                    $event->setResponse(new Response($result->toJson()));
                }
            }
        }
    }
}