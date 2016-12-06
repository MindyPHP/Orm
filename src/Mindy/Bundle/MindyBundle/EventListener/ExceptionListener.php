<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 26/10/16
 * Time: 15:26
 */

namespace Mindy\Bundle\MindyBundle\EventListener;

use Mindy\Template\Renderer;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Throwable;

class ExceptionListener
{
    protected $template;
    protected $logger;
    protected $path;

    public function __construct(Renderer $template, LoggerInterface $logger, $path = 'mindy/error/%s.html')
    {
        $this->template = $template;
        $this->logger = $logger;
        $this->path = $path;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getException();

        // Log exception
        $this->logException($exception);

        // Customize your response object to display the exception details
        $response = new Response();

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else if ($exception instanceof AccessDeniedException || $exception instanceof InvalidCsrfTokenException) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response->setContent($this->renderException($exception));
        $event->setResponse($response);
    }

    protected function renderException(Throwable $exception)
    {
        $code = Response::HTTP_INTERNAL_SERVER_ERROR;
        if ($exception instanceof HttpExceptionInterface) {
            $code = $exception->getStatusCode();
        } else if ($exception instanceof AccessDeniedException || $exception instanceof InvalidCsrfTokenException) {
            $code = Response::HTTP_FORBIDDEN;
        }
        return $this->template->render(sprintf($this->path, $code), [
            'exception' => $exception
        ]);
    }

    protected function logException(Throwable $exception)
    {
        $this->logger->error($exception->getMessage(), [
            'line' => $exception->getLine(),
            'file' => $exception->getFile()
        ]);
    }
}