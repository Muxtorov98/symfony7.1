<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 50],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        switch (true) {
            case $exception instanceof AccessDeniedHttpException:
                $this->handleAccessDeniedHttpException($event);
                break;

            case $exception instanceof HttpExceptionInterface:
                $this->handleHttpException($event, $exception);
                break;

            case $exception instanceof ValidationFailedException:
                $this->handleValidationException($event, $exception);
                break;

            default:
                $this->handleGenericException($event, $exception);
        }
    }

    private function handleAccessDeniedHttpException(ExceptionEvent $event): void
    {
        $response = new JsonResponse(
            $this->normalize(Response::HTTP_FORBIDDEN, 'You do not have the required permissions.', 'Access Denied'),
            Response::HTTP_FORBIDDEN
        );

        $event->setResponse($response);
    }

    private function handleHttpException(ExceptionEvent $event, HttpExceptionInterface $exception): void
    {
        $response = new JsonResponse(
            $this->normalize($exception->getStatusCode(), $exception->getMessage()),
            $exception->getStatusCode()
        );

        $event->setResponse($response);
    }

    private function handleGenericException(ExceptionEvent $event, Throwable $exception): void
    {
        $response = new JsonResponse(
            $this->normalize(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage()),
            Response::HTTP_INTERNAL_SERVER_ERROR
        );

        $event->setResponse($response);
    }

    private function handleValidationException(ExceptionEvent $event, ValidationFailedException $exception): void
    {
        $violations = $exception->getViolations();
        $errors = [];

        foreach ($violations as $violation) {
            $errors[] = [
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'title' => 'Validation Error',
                'description' => $violation->getMessage(),
            ];
        }

        $response = new JsonResponse(
            $errors[0] ?? ['code' => Response::HTTP_UNPROCESSABLE_ENTITY],
            $errors[0]['code'] ?? Response::HTTP_UNPROCESSABLE_ENTITY
        );

        $event->setResponse($response);
    }

    private function normalize(int $statusCode, string $description, string $title = 'An error occurred'): array
    {
        return [
            'code' => $statusCode,
            'title' => $title,
            'description' => $description,
        ];
    }
}
