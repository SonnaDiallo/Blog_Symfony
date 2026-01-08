<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ActivityLogSubscriber implements EventSubscriberInterface
{
    private float $startTime;

    public function __construct(
        private LoggerInterface $logger,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100],
            KernelEvents::RESPONSE => ['onKernelResponse', -100],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->startTime = microtime(true);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        // Ne pas logger les assets statiques
        $path = $request->getPathInfo();
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$/i', $path)) {
            return;
        }

        $duration = round((microtime(true) - $this->startTime) * 1000, 2);
        $user = $this->getUsername();

        $this->logger->info('HTTP Request', [
            'method' => $request->getMethod(),
            'path' => $path,
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'user' => $user,
            'ip' => $request->getClientIp(),
            'user_agent' => substr($request->headers->get('User-Agent', ''), 0, 100),
        ]);
    }

    private function getUsername(): string
    {
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser()) {
            return $token->getUser()->getUserIdentifier();
        }
        return 'anonymous';
    }
}
