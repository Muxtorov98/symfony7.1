<?php

namespace App\Controller\Base;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ErrorController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function showError(): Response
    {
        $request = $this->requestStack->getCurrentRequest();
        $exception = $request->attributes->get('exception');
        $statusCode = $exception instanceof HttpException ? $exception->getStatusCode() : 500;
        $errorMessage = $exception ? $exception->getMessage() : 'Unknown Error';

        if ($statusCode == '404'){
            return $this->render('404.html.twig');
        }

        return new JsonResponse([
            'statusCode' => $statusCode,
            'messageResponse' => $errorMessage,
        ], $statusCode);
    }
}