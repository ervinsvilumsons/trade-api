<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;

        $message = $exception instanceof HttpExceptionInterface
            ? $exception->getMessage()
            : 'Internal Server Error';

        $response = new JsonResponse([
            'error' => $message,
        ], $statusCode);

        $event->setResponse($response);
    }
}