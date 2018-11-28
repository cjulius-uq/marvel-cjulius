<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;


class JsonExceptionsController {
    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
    {
        $httpCode = $exception->getCode() ? $exception->getCode() : $exception->getStatusCode();

        $response = new JsonResponse([
            'error' => true,
            'message' => $exception->getMessage(),
        ]);
        $response->headers->set('X-Status-Code', $httpCode);
        return $response;
    }
}